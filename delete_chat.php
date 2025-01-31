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

$response = array('status' => 'error', 'message' => '');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['chat_id']) && isset($_SESSION['username'])) {
    $chat_id = $_POST['chat_id'];
    $username = $_SESSION['username'];

    $sql_check_creator = "SELECT * FROM chats WHERE id='$chat_id' AND creator='$username'";
    $result_check_creator = $conn->query($sql_check_creator);

    if ($result_check_creator->num_rows > 0) {
        $sql_delete_messages = "DELETE FROM private_messages WHERE chat_id='$chat_id'";
        $conn->query($sql_delete_messages);

        $sql_delete_participants = "DELETE FROM chat_participants WHERE chat_id='$chat_id'";
        $conn->query($sql_delete_participants);

        $sql_delete_chat = "DELETE FROM chats WHERE id='$chat_id'";
        if ($conn->query($sql_delete_chat) === TRUE) {
            $response['status'] = 'success';
            $response['message'] = 'Chat eliminado correctamente.';
        } else {
            $response['message'] = 'Error al eliminar el chat.';
        }
    } else {
        $response['message'] = 'No tienes permiso para eliminar este chat.';
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>