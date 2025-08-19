<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$id = $_GET['id'] ?? 0;
$id = intval($id);

if (!$id) {
    header("Location: index.php");
    exit;
}

// Fetch current task data
$sql = "SELECT * FROM tasks WHERE id=$id LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows !== 1) {
    echo "Task not found.";
    exit;
}
$task = $result->fetch_assoc();

// Get project_id for member list
$project_id = $task['project_id'];

// Fetch project members + creator
$members = [];
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
$result_members = $stmt->get_result();
while ($row = $result_members->fetch_assoc()) {
    $members[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $due_date = $_POST['due_date'];
    $assigned_to = isset($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : NULL;

    // Check if assigned_to changed
    $previous_assigned = $task['assigned_to'];

    $sql = "UPDATE tasks 
            SET title='$title', description='$description', due_date='$due_date', assigned_to=" . ($assigned_to ?: "NULL") . " 
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {

        // Send notification if assigned_to is set, not null, and different from previous
        if ($assigned_to && $assigned_to != $task['user_id'] && $assigned_to != $previous_assigned) {
            $stmt = $conn->prepare("
                INSERT INTO notifications (user_id, message, project_id, type, created_at)
                VALUES (?, ?, ?, 'task_assigned', NOW())
            ");
            $msg = "You have been assigned a task: '$title'.";
            $stmt->bind_param("isi", $assigned_to, $msg, $project_id);
            $stmt->execute();
        }

        header("Location: index.php?project_id=$project_id");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="javascript:history.back()" class="back-button">&#8592; Back</a>
    <h1>Edit Task</h1>
    <form method="POST" action="">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br>

        <label>Description:</label><br>
        <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea><br>

        <label>Due Date:</label><br>
        <input type="datetime-local" name="due_date" value="<?php echo htmlspecialchars($task['due_date']); ?>"><br>

        <label>Assign to:</label><br>
        <select name="assigned_to">
            <option value="">-- Unassigned --</option>
            <?php foreach ($members as $member): ?>
                <option value="<?php echo $member['id']; ?>" <?php echo ($task['assigned_to'] == $member['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($member['username']); ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Update Task</button>
    </form>
</body>
</html>
