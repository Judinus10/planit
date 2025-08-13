<?php
require 'db.php';
session_start();

$id = intval($_GET['id']);
$task_id = intval($_GET['task_id']);
$user_id = $_SESSION['user_id'];

// Verify subtask belongs to the user’s task before deleting
$check_sql = "SELECT 1 FROM tasks 
              JOIN subtasks ON subtasks.task_id = tasks.id
              WHERE subtasks.id = $id AND tasks.user_id = $user_id";

$result = $conn->query($check_sql);

if ($result && $result->num_rows > 0) {
    $delete_sql = "DELETE FROM subtasks WHERE id = $id";
    $conn->query($delete_sql);
}

header("Location: index.php");
exit;
?>
