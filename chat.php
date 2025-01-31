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

$chat_background = 'default_background.png';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql = "SELECT chat_background FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
    if ($user && !empty($user['chat_background'])) {
        $chat_background = $user['chat_background'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['username'])) {
    $message = $_POST['message'];
    $reply_to = isset($_POST['reply_to']) ? $_POST['reply_to'] : null;
    $username = $_SESSION['username'];
    if (strlen($message) <= 500) {
        $sql = "INSERT INTO messages (username, content, reply_to) VALUES ('$username', '$message', '$reply_to')";
        $conn->query($sql);
    }
}

$sql = "SELECT users.username, users.profile_image, messages.id, messages.content, messages.timestamp, messages.reply_to 
        FROM messages 
        JOIN users ON messages.username = users.username 
        ORDER BY messages.timestamp ASC";
$result = $conn->query($sql);

$messages = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Chat Público</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('uploads/<?php echo htmlspecialchars($chat_background); ?>');
            background-size: cover;
            background-position: center;
            background-color: #f2f2f2;
        }
        .chat-container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        h1 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }
        #search-container {
            display: flex;
            margin-bottom: 20px;
        }
        #search-user {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        #search-button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        #search-button:hover {
            background-color: #0056b3;
        }
        #chat-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
            height: 300px;
            overflow-y: scroll;
            margin-bottom: 10px;
            padding: 10px;
        }
        .message {
    display: flex;
    align-items: center;
    margin-bottom: 9px;
    background-color: #fff;
    padding: 8px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    border: 1px solid #ddd;
    position: relative; /* Para permitir el posicionamiento del botón */
}

.reply-button {
    background-color: #007BFF;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-left: 10px; /* Asegura que haya espacio entre el botón y el texto */
    position: absolute;
    right: 10px;
}
.reply-button:hover {
    background-color: #0056b3;
        }
        .message img {
            border-radius: 50%;
            margin-right: 10px;
            width: 40px;
            height: 40px;
        }
        .message .content {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .message a {
            text-decoration: none;
            color: #007BFF;
            font-weight: bold;
        }
        .message a:hover {
            text-decoration: underline;
        }
        .reply-content {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
            padding: 5px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        #message-form {
            display: flex;
            margin-bottom: 20px;
        }
        #message {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }
        button {
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .button-container {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .button-container a, .button-container button {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .button-container a:hover, .button-container button:hover {
            background-color: #c82333;
        }
        .edit-profile-button {
            background-color: #28a745;
            border: 1px solid #28a745;
        }
        .edit-profile-button:hover {
            background-color: #218838;
        }
        .social-space-button {
            background-color: #17a2b8;
            border: 1px solid #17a2b8;
        }
        .social-space-button:hover {
            background-color: #138496;
        }
    </style>
</head>
<body>
    <!-- Aquí empieza el contenido de tu cuerpo -->
    <div class="chat-container">
        <h1>Bienvenido/a <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'invitado'; ?> a ChatPúblico</h1>
        
        <div id="search-container">
            <input type="text" id="search-user" placeholder="Buscar usuario..." minlength="1">
            <button id="search-button" onclick="searchUser()">Buscar</button>
        </div>
        
        <div id="chat-box">
            <?php foreach ($messages as $message) { ?>
                <div class="message">
                    <img src="uploads/<?php echo !empty($message['profile_image']) ? htmlspecialchars($message['profile_image']) : 'default.png'; ?>" alt="Imagen de perfil">
                    <div>
                        <p><strong><a href="view_profile.php?username=<?php echo htmlspecialchars($message['username']); ?>"><?php echo htmlspecialchars($message['username']); ?></a>:</strong> <?php echo htmlspecialchars($message['content']); ?></p>
                        <?php if ($message['reply_to']) {
                            $reply_sql = "SELECT users.username, messages.content 
                                          FROM messages 
                                          JOIN users ON messages.username = users.username 
                                          WHERE messages.id = '" . $message['reply_to'] . "'";
                            $reply_result = $conn->query($reply_sql);
                            if ($reply_result->num_rows > 0) {
                                $reply_message = $reply_result->fetch_assoc();
                                echo '<div class="reply-content"><strong>' . htmlspecialchars($reply_message['username']) . ':</strong> ' . htmlspecialchars($reply_message['content']) . '</div>';
                            }
                        } ?>
                        <button class="reply-button" onclick="replyMessage(<?php echo $message['id']; ?>)">R</button>
                    </div>
                </div>
            <?php } ?>
        </div>
        
        <?php if (isset($_SESSION['username'])) { ?>
            <form id="message-form" action="chat.php" method="post">
                <input type="hidden" id="reply_to" name="reply_to" value="">
                <input type="text" id="message" name="message" placeholder="Escribe tu mensaje aquí (máximo 500 caracteres)" maxlength="500">
                <button type="submit">Enviar</button>
            </form>
                    <div class="button-container">
                        <a href="profile.php" class="edit-profile-button">Editar Perfil</a>
                        <button onclick="confirmLogout()">Cerrar Sesión</button>
                        <a href="social_space.php" class="social-space-button">Espacio Social</a>
                    </div>
                <?php } else { ?>
                    <div class="auth-links">
                        <a href="login.html">Iniciar Sesión</a>
                        <a href="register.html">Regístrate</a>
                    </div>
                <?php } ?>
            </div>

        <script>
    let shouldScroll = true;

    function replyMessage(messageId) {
        document.getElementById('reply_to').value = messageId;
    }

    function searchUser() {
        var username = document.getElementById('search-user').value;
        if (username.length >= 1) {
            window.location.href = 'search_user.php?username=' + encodeURIComponent(username);
        }
    }

    function confirmLogout() {
        if (confirm('¿Estás seguro de que quieres cerrar sesión?')) {
            window.location.href = 'logout.php';
        }
    }

    function loadMessages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'load_messages.php', true);
        xhr.onload = function() {
            if (this.status == 200) {
                var chatBox = document.getElementById('chat-box');
                var previousScrollHeight = chatBox.scrollHeight;
                chatBox.innerHTML = this.responseText;

                if (shouldScroll) {
                    chatBox.scrollTop = chatBox.scrollHeight;
                } else {
                    chatBox.scrollTop += chatBox.scrollHeight - previousScrollHeight;
                }
            }
        }
        xhr.send();
    }

    function enableAutoScroll() {
        shouldScroll = true;
    }

    document.getElementById('chat-box').addEventListener('scroll', function() {
        if (this.scrollTop + this.clientHeight < this.scrollHeight) {
            shouldScroll = false;
        } else {
            enableAutoScroll();
        }
    });

    setInterval(loadMessages, 2000);

    <?php if (isset($_SESSION['username'])) { ?>
    document.getElementById('message-form').addEventListener('submit', function(event) {
        event.preventDefault();
        var message = document.getElementById('message').value;
        var replyTo = document.getElementById('reply_to').value;

        if (message.length > 0 && message.length <= 500) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'send_message.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status == 200) {
                    var response = JSON.parse(this.responseText);
                    if (response.status === 'success') {
                        document.getElementById('message').value = '';
                        document.getElementById('reply_to').value = '';
                        enableAutoScroll();
                        loadMessages();
                    } else {
                        alert(response.message);
                    }
                }
            }
            xhr.send('message=' + encodeURIComponent(message) + '&reply_to=' + encodeURIComponent(replyTo));

            var sendButton = document.querySelector('button[type="submit"]');
            sendButton.disabled = true;
            setTimeout(function() {
                sendButton.disabled = false;
            }, 3000);
        }
    });
    <?php } ?>

    loadMessages();
</script>
</body>
</html>