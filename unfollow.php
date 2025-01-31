<?php
session_start();

$servername = "localhost";
$username = "database_username";
$password = "database_password";
$dbname = "database_name";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_SESSION['username']) && isset($_GET['username'])) {
    $follower = $_SESSION['username'];
    $followed = $_GET['username'];

    $sql_follower_id = "SELECT id FROM users WHERE username='$follower'";
    $result_follower_id = $conn->query($sql_follower_id);
    $follower_id = $result_follower_id->fetch_assoc()['id'];

    $sql_followed_id = "SELECT id FROM users WHERE username='$followed'";
    $result_followed_id = $conn->query($sql_followed_id);
    $followed_id = $result_followed_id->fetch_assoc()['id'];

    $sql_unfollow = "DELETE FROM followers WHERE follower_id='$follower_id' AND followed_id='$followed_id'";
    $conn->query($sql_unfollow);
}

$conn->close();

header("Location: view_profile.php?username=$followed");
exit();
?>