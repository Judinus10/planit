<?php
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = intval($_POST['task_id']);
    $title = $conn->real_escape_string($_POST['title']);

    // Insert subtask directly
    $sql = "INSERT INTO subtasks (task_id, title) VALUES ($task_id, '$title')";
    $conn->query($sql);

    // Get project_id for redirect
    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
    header("Location: index.php" . ($project_id ? "?project_id=$project_id" : ""));
    exit;
}
header("Location: index.php");
exit;
?>