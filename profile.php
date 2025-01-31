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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $bio = $_POST['bio'];
    $status = $_POST['status'];
    $profile_image = $_FILES['profile_image']['name'];
    $chat_background = $_FILES['chat_background']['name'];
    
    if ($profile_image) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_image);
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file);

        $sql = "UPDATE users SET profile_image='$profile_image' WHERE username='$username'";
        $conn->query($sql);
    }
    
    if ($chat_background) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($chat_background);
        move_uploaded_file($_FILES['chat_background']['tmp_name'], $target_file);

        $sql = "UPDATE users SET chat_background='$chat_background' WHERE username='$username'";
        $conn->query($sql);
    }

    $sql = "UPDATE users SET bio='$bio', status='$status' WHERE username='$username'";
    $conn->query($sql);

    $update_success = "Perfil actualizado exitosamente.";
}

$sql = "SELECT * FROM users WHERE username='" . $_SESSION['username'] . "'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (empty($user['profile_image'])) {
    $user['profile_image'] = 'default.png';
}

if (empty($user['chat_background'])) {
    $user['chat_background'] = 'default_background.png';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuario</title>
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
        }
        .profile-container img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
        }
        .profile-container h1 {
            margin-bottom: 20px;
        }
        .profile-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .profile-container label {
            margin-bottom: 5px;
            font-weight: bold;
            align-self: flex-start;
        }
        .profile-container input,
        .profile-container textarea,
        .profile-container select {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }
        .profile-container button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .profile-container button:hover {
            background-color: #0056b3;
        }
        .success-message {
            color: #28a745;
            margin-bottom: 15px;
            text-align: center;
            width: 100%;
        }
        .back-button {
            padding: 10px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            margin-top: 15px;
            display: inline-block;
        }
        .back-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Perfil de Usuario</h1>
        <?php if (isset($update_success)) { ?>
            <p class="success-message"><?php echo $update_success; ?></p>
        <?php } ?>
        <img id="profileImage" src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Imagen de perfil">
        <form action="profile.php" method="post" enctype="multipart/form-data">
            <label for="profile_image">Imagen de Perfil</label>
            <input type="file" id="profile_image" name="profile_image" onchange="previewImage(event)">
            <label for="bio">Biografía</label>
            <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
            <label for="status">Estado</label>
            <select id="status" name="status">
                <option value="Activo" <?php echo $user['status'] == 'Activo' ? 'selected' : ''; ?>>Activo</option>
                <option value="No molestar" <?php echo $user['status'] == 'No molestar' ? 'selected' : ''; ?>>No molestar</option>
                <option value="Desconectado" <?php echo $user['status'] == 'Desconectado' ? 'selected' : ''; ?>>Desconectado</option>
            </select>
            <label for="chat_background">Fondo del Chat</label>
            <input type="file" id="chat_background" name="chat_background" onchange="previewBackground(event)">
            <button type="submit">Guardar Cambios</button>
        </form>
        <a href="chat.php" class="back-button">Regresar al Chat</a>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('profileImage');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        function previewBackground(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.body;
                output.style.backgroundImage = "url('" + reader.result + "')";
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>