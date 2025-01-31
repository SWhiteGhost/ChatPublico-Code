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

$token = $_GET['token'];
$username = $_SESSION['username'];

$sql_check_invite = "SELECT invites.*, chats.chat_name, users.username AS creator, users.profile_image AS creator_image FROM invites 
                     JOIN chats ON invites.chat_id = chats.id 
                     JOIN users ON invites.created_by = users.username 
                     WHERE token='$token' AND expiry_time > NOW() AND (used_count < max_uses OR max_uses IS NULL)";
$result_check_invite = $conn->query($sql_check_invite);

if ($result_check_invite->num_rows > 0) {
    $invite = $result_check_invite->fetch_assoc();
    $chat_id = $invite['chat_id'];
    $chat_name = $invite['chat_name'];
    $creator = $invite['creator'];
    $creator_image = $invite['creator_image'];

    // Verificar si el usuario ya es miembro del chat
    $sql_check_membership = "SELECT * FROM chat_participants WHERE chat_id='$chat_id' AND user_id=(SELECT id FROM users WHERE username='$username')";
    $result_check_membership = $conn->query($sql_check_membership);

    if ($result_check_membership->num_rows > 0) {
        $already_member = true;
    } else {
        $already_member = false;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && !$already_member) {
        $sql_join_chat = "INSERT INTO chat_participants (chat_id, user_id) VALUES ('$chat_id', (SELECT id FROM users WHERE username='$username'))";
        if ($conn->query($sql_join_chat) === TRUE) {
            $sql_update_invite = "UPDATE invites SET used_count = used_count + 1 WHERE token='$token'";
            $conn->query($sql_update_invite);

            echo "Te has unido al chat correctamente.";
        } else {
            die("Error al unirse al chat: " . $conn->error);
        }
    }
} else {
    die("Invitación no válida o expirada.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aceptar Invitación</title>
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
        .accept-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        h1 {
            margin-bottom: 20px;
            color: #333;
        }
        .creator-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .creator-info img {
            border-radius: 50%;
            margin-right: 10px;
            width: 50px;
            height: 50px;
        }
        .creator-info p {
            margin: 0;
            color: #333;
        }
        p {
            color: #333;
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 15px;
        }
        button:hover {
            background-color: #218838;
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
    <div class="accept-container">
        <h1>Aceptar Invitación</h1>
        <div class="creator-info">
            <img src="uploads/<?php echo htmlspecialchars($creator_image); ?>" alt="Imagen de perfil">
            <p><strong><?php echo htmlspecialchars($creator); ?></strong> te ha invitado al chat <strong><?php echo htmlspecialchars($chat_name); ?></strong></p>
        </div>
        <?php if ($already_member) { ?>
            <p>Ya eres miembro de este chat.</p>
        <?php } else { ?>
            <form action="accept_invite.php?token=<?php echo htmlspecialchars($token); ?>" method="post">
                <button type="submit">Unirse al Chat</button>
            </form>
        <?php } ?>
        <a href="social_space.php" class="back-button">Regresar al Espacio Social</a>
    </div>
</body>
</html>