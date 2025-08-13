<?php
include 'db.php';

if (isset($_POST['id'], $_POST['status'])) {
    $id = (int) $_POST['id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);

    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "Error";
    }
}

if (isset($_POST['id'], $_POST['priority'])) {
    $id = (int) $_POST['id'];
    $priority = $_POST['priority'];

    $stmt = $conn->prepare("UPDATE tasks SET priority = ? WHERE id = ?");
    $stmt->bind_param("si", $priority, $id);

    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "Error";
    }
}
?>
