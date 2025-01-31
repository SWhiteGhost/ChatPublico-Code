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

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Chat Privado</title>
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
        .chat-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        .chat-container h1 {
            margin-bottom: 20px;
            color: #333;
        }
        .chat-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .chat-container label {
            margin-bottom: 5px;
            font-weight: bold;
            align-self: flex-start;
        }
        .chat-container input,
        .chat-container select {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }
        .chat-container button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .chat-container button:hover {
            background-color: #0056b3;
        }
        .back-button {
            margin-top: 10px;
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
    <div class="chat-container">
        <h1>Crear Chat Privado</h1>
        <form action="create_chat_action.php" method="post">
            <label for="chat_name">Nombre del Chat</label>
            <input type="text" id="chat_name" name="chat_name" required>
            
            <label for="participants">Integrantes (máximo 100)</label>
            <input type="number" id="participants" name="participants" min="1" max="100" required>
            
            <label for="privacy">Privacidad</label>
            <select id="privacy" name="privacy">
                <option value="private">Privado</option>
                <option value="public">Público</option>
            </select>
            
            <button type="submit">Crear Chat</button>
        </form>
        <a href="social_space.php" class="back-button">Regresar</a>
    </div>
</body>
</html>