<?php
require 'db.php';
session_start();

$id = intval($_POST['id']);
$status = $_POST['status'] === 'completed' ? 'completed' : 'pending';

// Update subtask status without checking user
$sql = "UPDATE subtasks 
        SET status = '$status' 
        WHERE id = $id
        LIMIT 1";

if ($conn->query($sql)) {
    echo "success";
} else {
    echo "Error: " . $conn->error;
}
?>
