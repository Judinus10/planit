<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications for this user
$sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC";
$result = $conn->query($sql);

// Mark all as read
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Notifications</h1>
    <ul>
        <?php if ($result->num_rows === 0): ?>
            <li>No notifications.</li>
        <?php else: ?>
            <?php while ($notif = $result->fetch_assoc()): ?>
                <li>
                    <?php echo htmlspecialchars($notif['message']); ?>
                    <small>(<?php echo $notif['created_at']; ?>)</small>
                </li>
            <?php endwhile; ?>
        <?php endif; ?>
    </ul>
</body>
</html>
