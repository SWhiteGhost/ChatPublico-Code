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

$response = array('status' => 'error', 'message' => '');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['username'])) {
    $chat_id = $_POST['chat_id'] ?? null;
    $message = $_POST['message'];
    $reply_to = isset($_POST['reply_to']) && $_POST['reply_to'] !== '' ? intval($_POST['reply_to']) : null;
    $username = $_SESSION['username'];

    if ($chat_id) {
        $sql_send_message = "INSERT INTO private_messages (chat_id, sender, message, reply_to) VALUES ('$chat_id', '$username', '$message', " . ($reply_to !== null ? "'$reply_to'" : "NULL") . ")";
    } else {
        $sql_send_message = "INSERT INTO messages (username, content, reply_to) VALUES ('$username', '$message', " . ($reply_to !== null ? "'$reply_to'" : "NULL") . ")";
    }

    if ($conn->query($sql_send_message) === TRUE) {
        $response['status'] = 'success';
        $response['message'] = 'Mensaje enviado';
    } else {
        $response['message'] = "Error al enviar el mensaje: " . $conn->error;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>