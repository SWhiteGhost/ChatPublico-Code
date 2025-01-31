<?php
$servername = "localhost";
$username = "database_username";
$password = "database_password";
$dbname = "database_name";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$message = "";
$registration_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($pass !== $confirm_pass) {
        $message = "Las contraseñas no coinciden.";
    } else {
        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

        $sql = "SELECT id FROM users WHERE username='$user' OR email='$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $message = "El usuario o correo ya existe.";
        } else {
            $sql = "INSERT INTO users (username, email, password) VALUES ('$user', '$email', '$hashed_pass')";
            if ($conn->query($sql) === TRUE) {
                $message = "Registro exitoso.";
                $registration_success = true;
            } else {
                $message = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado Registro</title>
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
        .message-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .message-container p {
            color: #333;
        }
        .message-container a {
            color: #007BFF;
            text-decoration: none;
        }
        .message-container a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="message-container">
        <p><?php echo $message; ?></p>
        <p><a href="register.html">Regresar</a></p>
        <?php if ($registration_success) { ?>
            <p><a href="login.html">Iniciar Sesión</a></p>
        <?php } ?>
    </div>
</body>
</html>