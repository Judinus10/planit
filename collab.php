<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Invite via email (for non-registered users)
    if (isset($_POST['invite_email'])) {
        $project_id = (int) $_POST['project_id'];
        $invite_email = trim($_POST['invite_email']);

        if (!empty($invite_email)) {
            $stmt = $conn->prepare("INSERT INTO invitations (project_id, email, invited_by) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $project_id, $invite_email, $user_id);
            $stmt->execute();

            $subject = "Project Collaboration Invitation";
            $link = "http://yourwebsite.com/register.php?project_id=$project_id";
            $body = "You have been invited to collaborate on a project. Click here to join: $link";
            mail($invite_email, $subject, $body);

            $message = "Invitation sent to $invite_email";
        }
    }

    // Invite existing user (send notification)
    if (isset($_POST['add_member'])) {
        $project_id = (int) $_POST['project_id'];
        $member_id = (int) $_POST['member_id'];

        // Check if user is already a member
        $check = $conn->prepare("SELECT id FROM project_members WHERE project_id=? AND user_id=?");
        $check->bind_param("ii", $project_id, $member_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 0) {
            // Send notification instead of adding directly
            $project_stmt = $conn->prepare("SELECT name FROM projects WHERE id=?");
            $project_stmt->bind_param("i", $project_id);
            $project_stmt->execute();
            $project_stmt->bind_result($project_name);
            $project_stmt->fetch();
            $project_stmt->close();

            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, project_id, type, created_at) VALUES (?, ?, ?, 'project_invite', NOW())");
            $msg = "You are invited to join the project '$project_name'.";
            $notif_stmt->bind_param("isi", $member_id, $msg, $project_id);
            $notif_stmt->execute();
            $notif_stmt->close();

            $message = "Invitation sent to user successfully.";
        } else {
            $message = "User is already a member of this project.";
        }
    }
}

// Fetch all projects created by current user
$projects_result = $conn->query("SELECT id, name FROM projects WHERE created_by=$user_id");

// Fetch all users except current
$users_result = $conn->query("SELECT id, username FROM users WHERE id != $user_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Collaborate on Projects</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="javascript:history.back()" class="back-button">&#8592; Back</a>

<h1>Collaborate on Projects</h1>
<?php if ($message) echo "<p class='success'>$message</p>"; ?>

<div class="actions">
    <button id="inviteBtn">Invite User (Email)</button>
    <button id="addBtn">Invite Existing User</button>
</div>

<!-- Invite via Email -->
<div class="section" id="inviteSection" style="display:none;">
    <form method="POST">
        <label>Select Project:</label>
        <select name="project_id" required>
            <?php while ($project = $projects_result->fetch_assoc()): ?>
                <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
            <?php endwhile; ?>
        </select><br>
        <label>Email:</label>
        <input type="email" name="invite_email" placeholder="Enter email" required>
        <button type="submit">Send Invitation</button>
    </form>
</div>

<!-- Invite existing user -->
<div class="section" id="addSection" style="display:none;">
    <form method="POST">
        <label>Select Project:</label>
        <select name="project_id" required>
            <?php $projects_result->data_seek(0); ?>
            <?php while ($project = $projects_result->fetch_assoc()): ?>
                <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
            <?php endwhile; ?>
        </select><br>
        <label>Select User:</label>
        <select name="member_id" required>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="add_member">Send Invitation</button>
    </form>
</div>

<script>
    const inviteBtn = document.getElementById('inviteBtn');
    const addBtn = document.getElementById('addBtn');
    const inviteSection = document.getElementById('inviteSection');
    const addSection = document.getElementById('addSection');

    inviteBtn.addEventListener('click', () => {
        inviteSection.style.display = 'block';
        addSection.style.display = 'none';
    });

    addBtn.addEventListener('click', () => {
        addSection.style.display = 'block';
        inviteSection.style.display = 'none';
    });
</script>

</body>
</html>
