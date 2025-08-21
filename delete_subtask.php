<?php
require 'db.php';
session_start();

$id = intval($_GET['id']);
$task_id = intval($_GET['task_id']);
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;

// Directly delete the subtask
$delete_sql = "DELETE FROM subtasks WHERE id = $id";
$conn->query($delete_sql);

// Redirect back with project_id
header("Location: index.php" . ($project_id ? "?project_id=$project_id" : ""));
exit;
?>