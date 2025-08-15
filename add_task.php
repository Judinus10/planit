<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;

// Fetch project members + creator for dropdown
$members = [];
if ($project_id) {
    $stmt = $conn->prepare("
        SELECT u.id, u.username 
        FROM users u
        WHERE u.id IN (
            SELECT user_id FROM project_members WHERE project_id = ?
            UNION 
            SELECT created_by FROM projects WHERE id = ?
        )
    ");
    $stmt->bind_param("ii", $project_id, $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $assigned_to = isset($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : NULL;

    $sql = "INSERT INTO tasks (title, description, due_date, status, priority, user_id, project_id, assigned_to)
            VALUES ('$title', '$description', '$due_date', 'To-do', '$priority', $user_id, $project_id, " . ($assigned_to ?: "NULL") . ")";

    if ($conn->query($sql) === TRUE) {
        $task_id = $conn->insert_id;

        // Send notification if assigned_to is set and not the creator
        if ($assigned_to && $assigned_to != $user_id) {
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, project_id, type, created_at)
                VALUES (?, ?, ?, 'task_assigned', NOW())
            ");
            $msg = "You have been assigned a new task: '$title'.";
            $stmt->bind_param("isi", $assigned_to, $msg, $project_id);
            $stmt->execute();
        }

        header("Location: index.php?project_id=$project_id");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Add Task</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="javascript:history.back()" class="back-button">&#8592; Back</a>
    <h1>Add New Task</h1>

    <form method="POST" action="">
        <label>Title:</label><br>
        <input type="text" name="title" required><br>

        <label>Description:</label><br>
        <textarea name="description"></textarea><br>

        <label>Due Date:</label><br>
        <input type="datetime-local" name="due_date" required><br>

        <label>Assign to:</label><br>
        <select name="assigned_to">
            <option value="">-- Unassigned --</option>
            <?php foreach ($members as $member): ?>
                <option value="<?php echo $member['id']; ?>">
                    <?php echo htmlspecialchars($member['username']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Priority:</label><br>
        <select name="priority">
            <option value="Low">Low</option>
            <option value="Medium" selected>Medium</option>
            <option value="High">High</option>
        </select><br><br>

        <button type="submit">Add Task</button>
    </form>
</body>
</html>
