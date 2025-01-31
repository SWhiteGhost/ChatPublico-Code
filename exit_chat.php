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

    $sql_check_participant = "SELECT * FROM chat_participants 
                              WHERE chat_id='$chat_id' AND user_id=(SELECT id FROM users WHERE username='$username')";
    $result_check_participant = $conn->query($sql_check_participant);

    if ($result_check_participant->num_rows > 0) {
        $sql_exit_chat = "DELETE FROM chat_participants 
                          WHERE chat_id='$chat_id' AND user_id=(SELECT id FROM users WHERE username='$username')";
        if ($conn->query($sql_exit_chat) === TRUE) {
            $response['status'] = 'success';
            $response['message'] = 'Has salido del chat correctamente.';
        } else {
            $response['message'] = 'Error al salir del chat.';
        }
    } else {
        $response['message'] = 'No eres parte de este chat.';
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>