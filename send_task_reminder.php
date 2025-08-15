<?php
session_start();
require 'db.php';
require 'send_email.php';
date_default_timezone_set('Asia/Colombo');

$now = date('Y-m-d H:i:s');
$oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));

// Fetch tasks due soon, not completed, not yet notified
$sql = "SELECT t.id, t.title, t.due_date, t.status,
               owner.email AS owner_email,
               assignee.email AS assignee_email
        FROM tasks t
        JOIN users owner ON t.user_id = owner.id           
        JOIN users assignee ON t.assigned_to = assignee.id 
        WHERE t.notified = 0
        AND t.status != 'completed'
        AND t.due_date <= DATE_ADD(?, INTERVAL 1 HOUR)
        AND t.due_date >= ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $now, $oneHourAgo);
$stmt->execute();
$result = $stmt->get_result();

while ($task = $result->fetch_assoc()) {
    $subject = "Reminder: Task '{$task['title']}' is due soon!";
    $message = "Hello,\n\nYour task '{$task['title']}' is due at {$task['due_date']}.\nAssigned to: {$task['assignee_username']}.\nPlease ensure it is completed on time.\n\n- Task Manager";

    // Send email to project owner
    if (sendEmail($task['owner_email'], $message, $subject)) {
        echo "✅ Email sent to owner ({$task['owner_email']}) for task ID {$task['id']}<br>";
    }

    // Send email to assignee
    if (sendEmail($task['assignee_email'], $message, $subject) && $task['assignee_email'] != $task['owner_email']) {
        echo "✅ Email sent to assignee ({$task['assignee_email']}) for task ID {$task['id']}<br>";
    }

    // Mark as notified
    $update = $conn->prepare("UPDATE tasks SET notified = 1 WHERE id = ?");
    $update->bind_param('i', $task['id']);
    $update->execute();
}
?>
