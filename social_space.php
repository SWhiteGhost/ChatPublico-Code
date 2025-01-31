<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$servername = "localhost";
$username = "database_username";
$password = "database_password";
$dbname = "database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$username = $_SESSION['username'];

$sql_chats = "SELECT chats.*, users.username AS creator 
              FROM chats 
              LEFT JOIN chat_participants ON chats.id = chat_participants.chat_id 
              LEFT JOIN users ON chats.creator = users.username 
              WHERE chats.creator = '$username' OR chat_participants.user_id = (SELECT id FROM users WHERE username = '$username')";
$result_chats = $conn->query($sql_chats);

if (!$result_chats) {
    die("Error en la consulta SQL: " . $conn->error);
}

$chats = [];
if ($result_chats->num_rows > 0) {
    while ($row = $result_chats->fetch_assoc()) {
        $chats[] = $row;
    }
}

$sql_check_limit = "SELECT COUNT(*) AS chat_count FROM chats WHERE creator='$username'";
$result_check_limit = $conn->query($sql_check_limit);

if (!$result_check_limit) {
    die("Error en la consulta SQL: " . $conn->error);
}

$chat_count = $result_check_limit->fetch_assoc()['chat_count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Espacio Social</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .social-space-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        .button-container {
            display: flex;
            justify-content: space-around;
            width: 100%;
            margin-bottom: 20px;
        }
        .button-container button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .button-container button:hover {
            background-color: #0056b3;
        }
        .chats-container {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            background-color: #fafafa;
            overflow-y: auto;
            max-height: 300px;
            margin-bottom: 20px;
        }
        .chats-container h2 {
            margin-top: 0;
        }
        .chat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #fff;
        }
        .chat p {
            margin: 0;
            flex: 1;
        }
        .chat a, .chat button {
            margin-left: 10px;
            padding: 5px 10px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            border: none;
        }
        .chat a:hover, .chat button:hover {
            background-color: #0056b3;
        }
        .delete-button {
            background-color: #dc3545;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
        .exit-button {
            background-color: #ffc107;
            color: black;
        }
        .exit-button:hover {
            background-color: #e0a800;
        }
        .back-button {
            padding: 10px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="social-space-container">
        <h1>Espacio Social</h1>
        <div class="button-container">
            <button onclick="location.href='create_private_chat.php'" <?php echo $chat_count >= 10 ? 'disabled' : ''; ?>>Crear Chat Privado</button>
            <button onclick="searchPublicChats()">Buscar Chats Públicos</button>
        </div>
        <div class="chats-container">
            <h2>Mis Chats</h2>
            <?php if (count($chats) > 0) {
                foreach ($chats as $chat) { ?>
                    <div class="chat">
                        <p><?php echo htmlspecialchars($chat['chat_name']); ?> (<?php echo htmlspecialchars($chat['privacy']); ?>)</p>
                        <a href="chat_room.php?chat_id=<?php echo htmlspecialchars($chat['id']); ?>">Entrar</a>
                        <?php if ($chat['creator'] == $username) { ?>
                            <button class="delete-button" onclick="confirmDelete(<?php echo $chat['id']; ?>)">Eliminar</button>
                        <?php } else { ?>
                            <button class="exit-button" onclick="confirmExit(<?php echo $chat['id']; ?>)">Salir</button>
                        <?php } ?>
                    </div>
                <?php } 
            } else { ?>
                <p>No tienes chats.</p>
            <?php } ?>
        </div>
        <a href="chat.php" class="back-button">Regresar al Chat</a>
    </div>

    <script>
        function searchPublicChats() {
            alert('Funcionalidad para buscar chats públicos próximamente.');
        }

        function confirmDelete(chatId) {
            if (confirm('¿Estás seguro de que quieres eliminar este chat? Esta acción no se puede deshacer y se perderán todos los mensajes.')) {
                deleteChat(chatId);
            }
        }

        function deleteChat(chatId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_chat.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status == 200) {
                    alert('Chat eliminado correctamente.');
                    location.reload();
                } else {
                    alert('Error al eliminar el chat. Por favor, inténtalo de nuevo.');
                }
            }
            xhr.send('chat_id=' + encodeURIComponent(chatId));
        }

        function confirmExit(chatId) {
            if (confirm('¿Estás seguro de que quieres salir de este chat? Esta acción no se puede deshacer.')) {
                exitChat(chatId);
            }
        }

        function exitChat(chatId) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'exit_chat.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status == 200) {
                    alert('Has salido del chat correctamente.');
                    location.reload();
                } else {
                    alert('Error al salir del chat. Por favor, inténtalo de nuevo.');
                }
            }
            xhr.send('chat_id=' + encodeURIComponent(chatId));
        }
    </script>
</body>
</html>