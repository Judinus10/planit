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

  $sql = "UPDATE tasks SET title='$title', description='$description', due_date='$due_date' WHERE id=$id";
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
  <a href="javascript:history.back()" class="back-button">
    &#8592; Back
  </a>
  <h1>Edit Task</h1>
  <form method="POST" action="">
    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br>

    <label>Description:</label><br>
    <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea><br>

    <label>Due Date:</label><br>
    <input type="datetime-local" name="due_date" value="<?php echo htmlspecialchars($task['due_date']); ?>"><br>

    <button type="submit">Update Task</button>
  </form>
  <br>
  <!-- <a href="index.php">Back to Task List</a> -->
</body>

</html>