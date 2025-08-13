<?php
session_start();
require 'db.php';

$errors = [];
if (!isset($_SESSION['otp'], $_SESSION['reg_email'])) {
    echo "No registration in progress.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = trim($_POST['otp']);

    // Check OTP expiration (5 minutes)
    if (time() - $_SESSION['otp_time'] > 300) {
        $errors[] = "OTP expired. Please register again.";
        session_destroy();
    } elseif ($user_otp == $_SESSION['otp']) {
        // OTP correct, insert user
        $username = $_SESSION['reg_username'];
        $email = $_SESSION['reg_email'];
        $password = $_SESSION['reg_password'];

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, email_verified) VALUES (?, ?, ?, 1)");
        $stmt->bind_param('sss', $username, $email, $password);

        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            echo "Registration successful! You can <a href='login.php'>login</a> now.";
            exit;
        } else {
            $errors[] = "Error creating user: " . $conn->error;
        }
    } else {
        $errors[] = "Invalid OTP, please try again.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
</head>
<body>
    <h1>Verify OTP</h1>
    <?php if ($errors): ?>
        <ul>
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    <?php endif; ?>
    <form method="POST" action="">
        <label>Enter OTP sent to your email:</label>
        <input type="text" name="otp" required maxlength="6">
        <button type="submit">Verify</button>
    </form>
</body>
</html>
