<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get selected project
$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

// Fetch project info
$project_name = "All Tasks";
if ($project_id) {
    // Check if the user is either the creator or a member of the project
    $stmt = $conn->prepare("
        SELECT name 
        FROM projects 
        WHERE id=? 
        AND (created_by=? OR id IN (
            SELECT project_id FROM project_members WHERE user_id=?
        ))
    ");
    $stmt->bind_param("iii", $project_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
        $project_name = htmlspecialchars($project['name']);
    } else {
        $project_id = 0; // invalid project, fallback
    }
}

// Fetch tasks for this project (with assigned user's name)
if ($project_id) {
    $sql = "SELECT t.*, u.username AS assigned_user 
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.project_id = $project_id 
            ORDER BY t.due_date ASC";
} else {
    $sql = "SELECT t.*, u.username AS assigned_user
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.user_id = $user_id 
            ORDER BY t.due_date ASC";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $project_name; ?> - Tasks</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="javascript:history.back()" class="back-button">&#8592; Back</a>
    
    <div class="top-right">
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="logout.php">Logout</a></p>
    </div>

    <h1>Tasks for: <?php echo $project_name; ?></h1>

    <a href="notification.php" style="margin-right:10px;">Notifications</a>
    <a href="add_task.php?project_id=<?php echo $project_id; ?>">+ Add New Task</a>
    <a href="collab.php?project_id=<?php echo $project_id; ?>">+ Add Member</a>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Due Date</th>
                <th>Assigned To</th> <!-- New column -->
                <th>Status</th>
                <th>Priority</th>
                <th colspan="3" style="text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows === 0): ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:20px;">No tasks yet.</td>
                </tr>
            <?php else: ?>
                <?php while ($task = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td><?php echo htmlspecialchars($task['description']); ?></td>
                        <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($task['assigned_user'] ?? 'Unassigned'); ?></td> <!-- Assigned user -->
                        <td>
                            <select onchange='updateStatus(<?php echo $task['id']; ?>, this.value)'>
                                <option value="To-do" <?php echo ($task['status'] == 'To-do') ? 'selected' : '' ?>>To-do</option>
                                <option value='on-progress' <?php echo ($task["status"] == 'on-progress') ? 'selected' : '' ?>>On-progress</option>
                                <option value="completed" <?php echo ($task["status"] == "completed") ? "selected" : '' ?>>Completed</option>
                            </select>
                        </td>
                        <td>
                            <select onchange='updatePriority(<?php echo $task['id']; ?>, this.value)'>
                                <option value="High" <?php echo ($task['priority'] == 'High') ? 'selected' : '' ?>>High</option>
                                <option value="Medium" <?php echo ($task["priority"] == 'Medium') ? 'selected' : '' ?>>Medium</option>
                                <option value='Low' <?php echo ($task["priority"] == 'Low') ? 'selected' : '' ?>>Low</option>
                            </select>
                        </td>
                        <td><a href="edit_task.php?id=<?php echo $task['id']; ?>">Edit</a></td>
                        <td><a href="delete_task.php?id=<?php echo $task['id']; ?>" onclick="return confirm('Delete this task?');">Delete</a></td>
                        <td><button class="btn" id="toggleSubtaskBtn">Add Subtask</button></td>
                    </tr>

                    <?php
                    // Fetch subtasks for this task
                    $task_id = $task['id'];
                    $subtask_sql = "SELECT * FROM subtasks WHERE task_id = $task_id ORDER BY created_at ASC";
                    $subtask_result = $conn->query($subtask_sql);

                    if ($subtask_result->num_rows > 0):
                    ?>
                        <tr>
                            <td colspan="9" style="padding-left: 40px; background: #f0eaff;">
                                <strong>Subtasks:</strong>
                                <ul style="margin-top: 8px;">
                                    <?php while ($subtask = $subtask_result->fetch_assoc()):
                                        $checked = ($subtask['status'] === 'completed') ? "checked" : "";
                                    ?>
                                        <li>
                                            <input type="checkbox" data-subtask-id="<?php echo $subtask['id']; ?>" class="subtask-toggle" <?php echo $checked; ?>>
                                            <?php echo htmlspecialchars($subtask['title']); ?>
                                            <a href="delete_subtask.php?id=<?php echo $subtask['id']; ?>&task_id=<?php echo $task_id; ?>" style="color:white; margin-left:8px;">Delete</a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endif; ?>

                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>

<script src="script.js"></script>
</body>
</html>
