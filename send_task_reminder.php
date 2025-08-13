<?php
session_start();
require 'db.php';
require 'send_email.php';
date_default_timezone_set('Asia/Colombo');

$now = date('Y-m-d H:i:s');
// Select tasks due in next hour for all users, not notified
$sql = "SELECT t.id, t.title, t.due_date, u.email
        FROM tasks t
        JOIN users u ON t.user_id = u.id
        WHERE t.notified = 0
        AND t.due_date <= DATE_ADD(?, INTERVAL 1 HOUR)
        AND t.due_date >= ?";

$stmt = $conn->prepare($sql);
$oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
$stmt->bind_param('ss', $now, $oneHourAgo);
$stmt->execute();
$result = $stmt->get_result();

// var_dump($now, $oneHourAgo); //for debugging
// echo $stmt->error;
// echo $stmt->num_rows; // only works if store_result() is called


while ($task = $result->fetch_assoc()) {
    $email = $task['email'];
    echo "hi";
    $subject = "Reminder: Task '{$task['title']}' is due soon!";
    $message = "Hello, \n\nYour task '{$task['title']}' is due at {$task['due_date']}.\nPlease complete it on time.\n\n- Task Manager";


    if (sendEmail($email, $message, $subject)) {
        echo "✅ Email sent to {$email} for task ID {$task['id']}<br>"; // for debugging
        $update = $conn->prepare("UPDATE tasks SET notified = 1 WHERE id = ?");
        $update->bind_param('i', $task['id']);
        $update->execute();
    }// else {
    //      echo "❌ Failed to send email to {$email} for task ID {$task['id']}<br>";// for debugging
    // }
}

?>
