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

$username = isset($_GET['username']) ? $_GET['username'] : $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username='$username'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("Usuario no encontrado.");
}

if (empty($user['profile_image'])) {
    $user['profile_image'] = 'default.png';
}

$sql_followers = "SELECT COUNT(*) AS followers FROM followers WHERE followed_id=(SELECT id FROM users WHERE username='$username')";
$sql_following = "SELECT COUNT(*) AS following FROM followers WHERE follower_id=(SELECT id FROM users WHERE username='$username')";
$result_followers = $conn->query($sql_followers);
$result_following = $conn->query($sql_following);
$followers = ($result_followers && $result_followers->num_rows > 0) ? $result_followers->fetch_assoc()['followers'] : 0;
$following = ($result_following && $result_following->num_rows > 0) ? $result_following->fetch_assoc()['following'] : 0;

$is_my_profile = ($_SESSION['username'] == $user['username']);
$is_following = false;

if (!$is_my_profile) {
    $sql_check_follow = "SELECT * FROM followers WHERE follower_id=(SELECT id FROM users WHERE username='" . $_SESSION['username'] . "') AND followed_id=(SELECT id FROM users WHERE username='$username')";
    $result_check_follow = $conn->query($sql_check_follow);
    $is_following = ($result_check_follow && $result_check_follow->num_rows > 0);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?php echo htmlspecialchars($user['username']); ?></title>
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
        .profile-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
            position: relative;
        }
        .profile-container img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
        }
        .profile-container h1 {
            margin-bottom: 10px;
            font-size: 24px;
            color: #333;
        }
        .profile-container p {
            margin-bottom: 10px;
            color: #666;
        }
        .profile-container .follow-info {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .profile-container .follow-info div {
            text-align: center;
        }
        .profile-container .follow-info div p {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .profile-container .follow-info div span {
            color: #666;
        }
        .profile-container a, .profile-container button {
            display: block;
            margin: 10px 0;
            color: #007BFF;
            text-decoration: none;
            padding: 10px;
            border: 1px solid #007BFF;
            border-radius: 5px;
            background-color: #fff;
            transition: background-color 0.3s;
        }
        .profile-container a:hover, .profile-container button:hover {
            background-color: #007BFF;
            color: #fff;
        }
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #dc3545;
            border: 1px solid #dc3545;
            color: white;
        }
        .back-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <a href="chat.php" class="back-button">Regresar</a>
        <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Imagen de perfil">
        <h1><?php echo htmlspecialchars($user['username']); ?></h1>
        <div class="follow-info">
            <div>
                <p><?php echo $followers; ?></p>
                <span>Seguidores</span>
            </div>
            <div>
                <p><?php echo $following; ?></p>
                <span>Seguidos</span>
            </div>
        </div>
        <p><?php echo htmlspecialchars($user['bio']); ?></p>
        <p><strong>Estado:</strong> <?php echo htmlspecialchars($user['status']); ?></p>
        <?php if (!$is_my_profile) { ?>
            <?php if ($is_following) { ?>
                <button onclick="location.href='unfollow.php?username=<?php echo $user['username']; ?>'">Dejar de Seguir</button>
            <?php } else { ?>
                <button onclick="location.href='follow.php?username=<?php echo $user['username']; ?>'">Seguir</button>
            <?php } ?>
        <?php } ?>
    </div>
</body>
</html>