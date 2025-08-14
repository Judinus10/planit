<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = (int) $_POST['task_id'];
    $invite_email = trim($_POST['invite_email'] ?? '');
    $invite_username = trim($_POST['invite_username'] ?? '');

    // Invite via email
    if (!empty($invite_email)) {
        // Save to a invitations table (optional)
        $stmt = $conn->prepare("INSERT INTO invitations (task_id, email, invited_by) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $task_id, $invite_email, $user_id);
        $stmt->execute();

        // Send email invitation
        $subject = "Task Collaboration Invitation";
        $link = "http://yourwebsite.com/register.php?task_id=$task_id"; // user registers to accept task
        $body = "You have been invited to collaborate on a task. Click here to join: $link";
        mail($invite_email, $subject, $body);

        $message .= "Invitation sent to $invite_email<br>";
    }

    // Invite existing user by username
    if (!empty($invite_username)) {
        $stmt = $conn->prepare("SELECT id, email FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param("s", $invite_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $invited_user = $result->fetch_assoc();
            $invited_id = $invited_user['id'];

            // Assign task to the invited user
            $stmt2 = $conn->prepare("UPDATE tasks SET user_id=? WHERE id=?");
            $stmt2->bind_param("ii", $invited_id, $task_id);
            $stmt2->execute();

            $message .= "Task assigned to $invite_username<br>";
        } else {
            $message .= "User $invite_username not found.<br>";
        }
    }
}

// Fetch tasks created by current user
$tasks_result = $conn->query("SELECT * FROM tasks WHERE user_id=$user_id ORDER BY due_date ASC");

?>

<!DOCTYPE html>
<html>

<head>
    <title>Collaborate on Tasks</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="top-right">
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="logout.php">Logout</a></p>
    </div>

    <h1>Invite Members to Tasks</h1>
    <a href="javascript:history.back()" class="back-button">
        &#8592; Back
    </a>

    <?php if ($message)
        echo "<p style='color:green;'>$message</p>"; ?>

    <table>
        <thead>
            <tr>
                <th>Task</th>
                <th>Due Date</th>
                <th>Invite by Email</th>
                <th>Assign to User</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($task = $tasks_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                    <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="email" name="invite_email" placeholder="Invite by email">
                            <button type="submit">Send</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="text" name="invite_username" placeholder="Assign by username">
                            <button type="submit">Assign</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>

</html>