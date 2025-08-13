<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];
    $priority = $_POST['priority'];

    $user_id = $_SESSION['user_id'];
    $sql = "INSERT INTO tasks (title, description, due_date, status, priority, user_id) VALUES ('$title', '$description', '$due_date', '$status', '$priority', $user_id)";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
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
  <h1>Add New Task</h1>
  <form method="POST" action="">
    <label>Title:</label><br>
    <input type="text" name="title" required><br>

    <label>Description:</label><br>
    <textarea name="description"></textarea><br>

    <label>Due Date:</label><br>
    <input type="date" name="due_date"><br>

    <label>Priority:</label><br>
    <select name="priority">
      <option value="low">Low</option>
      <option value="medium" selected>Medium</option>
      <option value="high">High</option>
    </select><br><br>

    <button type="submit">Add Task</button>
  </form>
  <br>
  <a href="index.php">Back to Task List</a>
</body>
</html>
