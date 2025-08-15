<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle accept/decline actions for project invites
if (isset($_GET['action']) && isset($_GET['project_id'])) {
    $project_id = (int)$_GET['project_id'];
    $action = $_GET['action'];

    if ($action === 'accept') {
        // Add user to project_members if not already
        $stmt = $conn->prepare("INSERT IGNORE INTO project_members (project_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $project_id, $user_id);
        $stmt->execute();
    }

    // Remove the project invite notification
    $stmt = $conn->prepare("DELETE FROM notifications WHERE user_id=? AND project_id=? AND type='project_invite'");
    $stmt->bind_param("ii", $user_id, $project_id);
    $stmt->execute();

    header("Location: notification.php");
    exit;
}

// Fetch all notifications for this user
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="javascript:history.back()" class="back-button">&#8592; Back</a>
<h1>Notifications</h1>
<ul>
    <?php if ($result->num_rows === 0): ?>
        <li>No notifications.</li>
    <?php else: ?>
        <?php while ($notif = $result->fetch_assoc()): ?>
            <li>
                <?php echo htmlspecialchars($notif['message']); ?>

                <?php if ($notif['type'] === 'project_invite'): ?>
                    <a href="notification.php?action=accept&project_id=<?php echo $notif['project_id']; ?>">Accept</a>
                    <a href="notification.php?action=decline&project_id=<?php echo $notif['project_id']; ?>">Decline</a>
                <?php endif; ?>

                <?php if ($notif['type'] === 'task_assigned'): ?>
                    <!-- Optional: Mark task notification as read -->
                    <form method="POST" style="display:inline;">
                        <button type="submit" name="mark_read" value="<?php echo $notif['id']; ?>">Mark as read</button>
                    </form>
                <?php endif; ?>

                <small>(<?php echo $notif['created_at']; ?>)</small>
            </li>
        <?php endwhile; ?>
    <?php endif; ?>
</ul>

<?php
// Handle marking task notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notif_id = (int)$_POST['mark_read'];
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id=? AND user_id=? AND type='task_assigned'");
    $stmt->bind_param("ii", $notif_id, $user_id);
    $stmt->execute();
    header("Location: notification.php");
    exit;
}
?>
</body>
</html>
