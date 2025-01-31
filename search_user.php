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

$search_username = $_GET['username'];
$sql = "SELECT * FROM users WHERE username LIKE '%$search_username%'";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Buscar Usuarios</title>
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
        .search-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        .search-container h1 {
            margin-bottom: 20px;
        }
        .search-container ul {
            list-style-type: none;
            padding: 0;
        }
        .user-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            background-color: #f9f9f9;
            transition: background-color 0.3s;
        }
        .user-item:hover {
            background-color: #e9e9e9;
        }
        .user-item img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }
        .user-item a {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }
        .user-item a:hover {
            text-decoration: underline;
        }
        .back-button {
            display: block;
            margin-top: 20px;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="search-container">
        <h1>Resultados de la Búsqueda</h1>
        <ul>
            <?php if (count($users) > 0) {
                foreach ($users as $user) { ?>
                    <li class="user-item">
                        <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Imagen de perfil">
                        <a href="view_profile.php?username=<?php echo htmlspecialchars($user['username']); ?>"><?php echo htmlspecialchars($user['username']); ?></a>
                    </li>
                <?php } 
            } else { ?>
                <li>No se encontraron usuarios.</li>
            <?php } ?>
        </ul>
        <a href="chat.php" class="back-button">Regresar al Chat</a>
    </div>
</body>
</html>