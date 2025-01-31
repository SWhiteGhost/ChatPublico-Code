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

foreach ($messages as $message) { ?>
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
<?php }

$conn->close();
?>