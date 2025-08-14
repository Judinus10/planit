<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle accept/decline
if (isset($_GET['action']) && isset($_GET['project_id'])) {
    $project_id = (int)$_GET['project_id'];
    $action = $_GET['action'];

    if ($action === 'accept') {
        // Add to project_members
        $stmt = $conn->prepare("INSERT IGNORE INTO project_members (project_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $project_id, $user_id);
        $stmt->execute();
    }
    // Remove notification
    $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id=? AND project_id=? AND type='project_invite'");
    $stmt->bind_param("ii", $user_id, $project_id);
    $stmt->execute();

    header("Location: notifications.php");
    exit;
}

// Fetch notifications
$result = $conn->query("SELECT * FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC");
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
                <?php if ($notif['type'] === 'project_invite'): ?>
                    <a href="notifications.php?action=accept&project_id=<?php echo $notif['project_id']; ?>">Accept</a>
                    <a href="notifications.php?action=decline&project_id=<?php echo $notif['project_id']; ?>">Decline</a>
                <?php endif; ?>
                <small>(<?php echo $notif['created_at']; ?>)</small>
            </li>
        <?php endwhile; ?>
    <?php endif; ?>
</ul>
</body>
</html>
