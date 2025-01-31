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

$chat_id = $_GET['chat_id'];
$username = $_SESSION['username'];

$sql_check_creator = "SELECT * FROM chats WHERE id='$chat_id' AND creator='$username'";
$result_check_creator = $conn->query($sql_check_creator);

if ($result_check_creator->num_rows == 0) {
    die("No tienes permiso para generar una invitación para este chat.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $expires_in = $_POST['expires_in']; // Duración en minutos
    $max_uses = $_POST['max_uses'];

    $token = bin2hex(random_bytes(16));
    $expiry_time = date("Y-m-d H:i:s", strtotime("+$expires_in minutes"));

    $sql_create_invite = "INSERT INTO invites (chat_id, token, expiry_time, max_uses, created_by) VALUES ('$chat_id', '$token', '$expiry_time', '$max_uses', '$username')";
    if ($conn->query($sql_create_invite) === TRUE) {
        $invite_link = "http://tudominio/accept_invite.php?token=$token";
    } else {
        die("Error al crear la invitación: " . $conn->error);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Invitación</title>
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
        .invite-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        label {
            margin: 10px 0 5px 0;
            color: #333;
        }
        input, select {
            padding: 10px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .button-section {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .invite-link-container {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .invite-link {
            word-wrap: break-word;
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            width: 100%;
            text-align: center;
            color: #333;
        }
        .copy-button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .copy-button:hover {
            background-color: #218838;
        }
        .back-button {
            padding: 10px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin-top: 15px;
        }
        .back-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="invite-container">
        <h1>Generar Invitación para Chat</h1>
        <form action="generate_invite.php?chat_id=<?php echo htmlspecialchars($chat_id); ?>" method="post">
            <label for="expires_in">Duración (en minutos):</label>
            <input type="number" id="expires_in" name="expires_in" required>
            <label for="max_uses">Usos máximos:</label>
            <select id="max_uses" name="max_uses" required>
                <option value="1">Un solo uso</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="200">200</option>
                <option value="500">500</option>
                <option value="1000">1000</option>
            </select>
            <div class="button-section">
                <button type="submit">Generar Invitación</button>
                <a href="chat_room.php?chat_id=<?php echo htmlspecialchars($chat_id); ?>" class="back-button">Regresar al Chat</a>
            </div>
        </form>
        <?php if (isset($invite_link)) { ?>
            <div class="invite-link-container">
                <p class="invite-link" id="inviteLink"><?php echo $invite_link; ?></p>
                <button class="copy-button" onclick="copyInviteLink()">Copiar Enlace</button>
            </div>
        <?php } ?>
    </div>

    <script>
        function copyInviteLink() {
            const inviteLink = document.getElementById('inviteLink').innerText;
            navigator.clipboard.writeText(inviteLink).then(() => {
                alert('Enlace copiado al portapapeles.');
            }).catch(err => {
                console.error('Error al copiar el enlace: ', err);
            });
        }
    </script>
</body>
</html>