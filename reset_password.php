<?php
session_start();
require 'db.php';
require 'send_email.php';

$errors = [];
$success = "";

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo "Invalid or missing token.";
    exit;
}

// Check if token exists and is not expired
$stmt = $conn->prepare("SELECT pr.user_id, u.username FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token=? AND pr.expires >= ?");
$now = date("U");
$stmt->bind_param('si', $token, $now);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $username = $row['username'];
} else {
    echo "This reset link is invalid or has expired.";
    exit;
}

// Handle new password submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $errors[] = "Please fill in both password fields.";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $update->bind_param('si', $hashed_password, $user_id);

        if ($update->execute()) {
            // Delete the token after successful reset
            $del = $conn->prepare("DELETE FROM password_resets WHERE token=?");
            $del->bind_param('s', $token);
            $del->execute();

            $success = "Password reset successfully! <a href='login.php'>Login here</a>.";
        } else {
            $errors[] = "Failed to update password. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Reset Password</h1>

    <?php if ($errors): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
            </ul>
        </div>
    <?php elseif ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form method="POST" action="">
            <label>New Password:</label><br>
            <input type="password" name="new_password" required><br><br>

            <label>Confirm Password:</label><br>
            <input type="password" name="confirm_password" required><br><br>

            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>

    <p><a href="login.php">Back to Login</a></p>
</body>
</html>
