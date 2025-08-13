<?php
require 'db.php';
session_start();

$id = intval($_POST['id']);
$status = $_POST['status'] === 'completed' ? 'completed' : 'pending';
$user_id = $_SESSION['user_id'];

// Update status if subtask belongs to user’s task
$sql = "UPDATE subtasks 
        JOIN tasks ON subtasks.task_id = tasks.id
        SET subtasks.status = '$status' 
        WHERE subtasks.id = $id AND tasks.user_id = $user_id LIMIT 1";

$conn->query($sql);

echo "OK";
?>
