<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = intval($_POST['task_id']);
    $title = $conn->real_escape_string($_POST['title']);

    // Optionally check if task belongs to logged-in user
    $user_id = $_SESSION['user_id'];
    $check = $conn->query("SELECT id FROM tasks WHERE id=$task_id AND user_id=$user_id LIMIT 1");
    if ($check->num_rows === 1) {
        $sql = "INSERT INTO subtasks (task_id, title) VALUES ($task_id, '$title')";
        $conn->query($sql);
    }
}
header("Location: index.php");
exit;
?>
