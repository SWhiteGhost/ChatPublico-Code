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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['username'])) {
    $chat_name = $_POST['chat_name'];
    $participants = $_POST['participants'];
    $privacy = $_POST['privacy'];
    $creator = $_SESSION['username'];

    $sql_check_limit = "SELECT COUNT(*) AS chat_count FROM chats WHERE creator='$creator'";
    $result_check_limit = $conn->query($sql_check_limit);
    $chat_count = $result_check_limit->fetch_assoc()['chat_count'];

    if ($chat_count < 10) {
        $sql_create_chat = "INSERT INTO chats (chat_name, participants, privacy, creator) VALUES ('$chat_name', '$participants', '$privacy', '$creator')";
        if ($conn->query($sql_create_chat) === TRUE) {
            $message = "Chat creado exitosamente.";
        } else {
            $message = "Error al crear el chat: " . $conn->error;
        }
    } else {
        $message = "Has alcanzado el límite de creación de chats.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado de Crear Chat Privado</title>
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
        .result-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
        }
        .result-container h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .result-container p {
            margin-bottom: 20px;
        }
        .back-button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <h1>Resultado</h1>
        <p><?php echo $message; ?></p>
        <a href="social_space.php" class="back-button">Regresar al Espacio Social</a>
    </div>
</body>
</html>