<?php
$host = 'localhost';
$dbname = 'task_manager';
$username = 'root'; 
$password = '';     

// Create connection using mysqli
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
