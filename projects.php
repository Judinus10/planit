<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle Create Project
if (isset($_POST['create_project'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name) {
        $stmt = $conn->prepare("INSERT INTO projects (name, description, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $user_id);
        $stmt->execute();
        $message = "Project created successfully.";
    } else {
        $message = "Project name is required.";
    }
}

// Handle Update Project
if (isset($_POST['update_project'])) {
    $project_id = (int) $_POST['project_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name) {
        $stmt = $conn->prepare("UPDATE projects SET name=?, description=? WHERE id=? AND created_by=?");
        $stmt->bind_param("ssii", $name, $description, $project_id, $user_id);
        $stmt->execute();
        $message = "Project updated successfully.";
    } else {
        $message = "Project name is required.";
    }
}

// Handle Delete Project
if (isset($_GET['delete'])) {
    $project_id = (int) $_GET['delete'];

    // Delete tasks under project first
    $conn->query("DELETE FROM tasks WHERE project_id=$project_id");

    // Delete project members
    $conn->query("DELETE FROM project_members WHERE project_id=$project_id");

    // Delete invitations
    $conn->query("DELETE FROM invitations WHERE project_id=$project_id");

    // Delete project
    $conn->query("DELETE FROM projects WHERE id=$project_id AND created_by=$user_id");

    $message = "Project deleted successfully.";
}

// Fetch projects created by user or the member
$projects_result = $conn->query("
    SELECT DISTINCT p.* 
    FROM projects p
    LEFT JOIN project_members pm ON p.id = pm.project_id
    WHERE p.created_by = $user_id OR pm.user_id = $user_id
    ORDER BY p.created_at DESC
");

?>

<!DOCTYPE html>
<html>

<head>
    <title>My Projects</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="top-right">
        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
        <a href="edit_profile.php" title="Edit Profile">&#9998;</a> | <!-- pencil icon -->
        <a href="logout.php">Logout</a>
    </div>

    <a href="notification.php" style="margin-right:10px;">Notifications</a>

    <h1>My Projects</h1>

    <?php if ($message)
        echo "<p class='success'>$message</p>"; ?>

    <!-- Create Project Form -->
    <div class="section1">
        <h2>Create New Project</h2>
        <form method="POST">
            <label>Project Name:</label>
            <input type="text" name="name" required>
            <label>Description:</label>
            <textarea name="description"></textarea>
            <button type="submit" name="create_project">Create Project</button>
        </form>
    </div>

    <!-- Projects Table -->
    <div class="section1">
        <h2>Your Projects</h2>
        <?php if ($projects_result->num_rows == 0): ?>
            <p>No projects yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Role</th>
                        <th colspan="2" style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($project = $projects_result->fetch_assoc()): ?>
                        <tr class="clickable-row" data-href="index.php?project_id=<?php echo $project['id']; ?>">
                            <td><?php echo htmlspecialchars($project['name']); ?></td>
                            <td><?php echo htmlspecialchars($project['description']); ?></td>
                            <td><?php echo htmlspecialchars($project['created_at']); ?></td>
                            <td>
                                <?php echo ($project['created_by'] == $user_id) ? 'Owner' : 'Member'; ?>
                            </td>
                            <td>
                                <?php if ($project['created_by'] == $user_id): ?>
                                    <a href="projects.php?delete=<?php echo $project['id']; ?>"
                                        onclick="event.stopPropagation(); return confirm('Delete this project?')">Delete</a>
                                <?php else: ?>
                                    <!-- Not owner, no delete link -->
                                    &mdash;
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($project['created_by'] == $user_id): ?>
                                    <a href="collab.php?project_id=<?php echo $project['id']; ?>"
                                        onclick="event.stopPropagation();">Manage Members</a>
                                <?php else: ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll('.clickable-row');
            rows.forEach(row => {
                row.addEventListener('click', () => {
                    window.location.href = row.dataset.href;
                });
            });
        });
    </script>

    <script src="script.js"></script>
</body>

</html>