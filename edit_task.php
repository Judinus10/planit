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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];

    $sql = "UPDATE tasks SET title='$title', description='$description', due_date='$due_date', status='$status', priority='$priority' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Fetch current task data
$sql = "SELECT * FROM tasks WHERE id=$id LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows !== 1) {
    echo "Task not found.";
    exit;
}

$task = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Task</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Edit Task</h1>
  <form method="POST" action="">
    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br>

    <label>Description:</label><br>
    <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea><br>

    <label>Due Date:</label><br>
    <input type="date" name="due_date" value="<?php echo htmlspecialchars($task['due_date']); ?>"><br>

    <label>Status:</label><br>
    <select name="status">
      <option value="pending" <?php if($task['status'] == 'pending') echo 'selected'; ?>>Pending</option>
      <option value="completed" <?php if($task['status'] == 'completed') echo 'selected'; ?>>Completed</option>
    </select><br>

    <label>Priority:</label><br>
    <select name="priority">
      <option value="low" <?php if($task['priority'] == 'low') echo 'selected'; ?>>Low</option>
      <option value="medium" <?php if($task['priority'] == 'medium') echo 'selected'; ?>>Medium</option>
      <option value="high" <?php if($task['priority'] == 'high') echo 'selected'; ?>>High</option>
    </select><br><br>

    <button type="submit">Update Task</button>
  </form>
  <br>
  <a href="index.php">Back to Task List</a>
</body>
</html>
