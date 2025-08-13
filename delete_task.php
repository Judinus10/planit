<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$id = $_GET['id'] ?? 0;
$id = intval($id);

if ($id) {
    $sql = "DELETE FROM tasks WHERE id=$id";
    $conn->query($sql);
}

header("Location: index.php");
exit;
?>
