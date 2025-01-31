<?php
session_start();

$servername = "localhost";
$username = "database_username";
$password = "database_password";
$dbname = "database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$chat_id = $_GET['chat_id'];
$username = $_SESSION['username'];

$sql_check_access = "
    SELECT * FROM chats 
    LEFT JOIN chat_participants ON chats.id = chat_participants.chat_id
    WHERE (chats.creator = '$username' OR chat_participants.user_id = (SELECT id FROM users WHERE username = '$username'))
    AND chats.id = '$chat_id'
";
$result_check_access = $conn->query($sql_check_access);

if ($result_check_access->num_rows == 0) {
    die("No tienes acceso a este chat.");
}

$sql_chat_details = "SELECT * FROM chats WHERE id='$chat_id'";
$result_chat_details = $conn->query($sql_chat_details);
$chat_details = $result_chat_details->fetch_assoc();

$sql_messages = "SELECT private_messages.message, private_messages.sent_at, users.username, users.profile_image 
                 FROM private_messages 
                 JOIN users ON private_messages.sender = users.username 
                 WHERE private_messages.chat_id='$chat_id' 
                 ORDER BY private_messages.sent_at ASC";
$result_messages = $conn->query($sql_messages);

$messages = [];
if ($result_messages->num_rows > 0) {
    while ($row = $result_messages->fetch_assoc()) {
        $messages[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($chat_details['chat_name']); ?> - Chat</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('uploads/default_background.png');
            background-size: cover;
            background-position: center;
        }
        .chat-room-container {
            background-color: rgba(255, 255, 255, 0.9);
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
        .messages-container {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 20px;
            background-color: #fafafa;
            overflow-y: auto;
            max-height: 300px;
            margin-bottom: 20px;
        }
        .message {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .message img {
            border-radius: 50%;
            margin-right: 10px;
            width: 40px;
            height: 40px;
        }
        .message p {
            margin: 0;
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
        }
        .message .sender {
            font-weight: bold;
            margin-right: 5px;
        }
        .message-form {
            display: flex;
            width: 100%;
        }
        .message-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        .message-form button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .message-form button:hover {
            background-color: #0056b3;
        }
        .button-section {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .invite-button {
            padding: 10px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .invite-button:hover {
            background-color: #218838;
        }
        .settings-button {
            padding: 10px;
            background-color: #ffc107;
            color: black;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .settings-button:hover {
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
    <div class="chat-room-container">
        <h1>Bienvenido/a <?php echo htmlspecialchars($username); ?> al Chat <?php echo htmlspecialchars($chat_details['chat_name']); ?></h1>
        <div id="messages-container" class="messages-container">
            <?php if (count($messages) > 0) {
                foreach ($messages as $message) { ?>
                    <div class="message">
                        <img src="uploads/<?php echo htmlspecialchars($message['profile_image']); ?>" alt="Imagen de perfil">
                        <p><span class="sender"><?php echo htmlspecialchars($message['username']); ?>:</span> <?php echo htmlspecialchars($message['message']); ?></p>
                    </div>
                <?php } 
            } else { ?>
                <p>No hay mensajes aún. Sé el primero en enviar uno.</p>
            <?php } ?>
        </div>
        <form class="message-form" action="send_message.php" method="post">
            <input type="hidden" name="chat_id" value="<?php echo htmlspecialchars($chat_id); ?>">
            <input type="text" name="message" placeholder="Escribe tu mensaje..." required>
            <button type="submit">Enviar</button>
        </form>
        <?php if ($chat_details['creator'] == $username && $chat_details['privacy'] == 'private') { ?>
            <div class="button-section">
                <a href="generate_invite.php?chat_id=<?php echo htmlspecialchars($chat_id); ?>" class="invite-button">Generar Invitación</a>
                <a href="settings.php?chat_id=<?php echo htmlspecialchars($chat_id); ?>" class="settings-button">Configurar Chat</a>
            </div>
        <?php } ?>
        <a href="social_space.php" class="back-button">Regresar al Espacio Social</a>
    </div>

    <script>
        function loadMessages() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'load_messages.php?chat_id=<?php echo htmlspecialchars($chat_id); ?>', true);
            xhr.onload = function() {
                if (this.status == 200) {
                    var chatBox = document.getElementById('messages-container');
                    chatBox.innerHTML = this.responseText;
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            }
            xhr.send();
        }

        setInterval(loadMessages, 2000);

        document.querySelector('.message-form').addEventListener('submit', function(event) {
            event.preventDefault();
            var message = document.querySelector('[name="message"]').value;
            var chatId = document.querySelector('[name="chat_id"]').value;
            
            if (message.trim() !== '') {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'send_message.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status == 200) {
                        var response = JSON.parse(this.responseText);
                        if (response.status === 'success') {
                            document.querySelector('[name="message"]').value = '';
                            loadMessages();
                        } else {
                            alert(response.message);
                        }
                    }
                }
                xhr.send('chat_id=' + encodeURIComponent(chatId) + '&message=' + encodeURIComponent(message));
            }
        });
    </script>
</body>
</html>