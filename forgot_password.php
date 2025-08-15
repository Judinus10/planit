<?php
session_start();
require 'db.php';
require 'send_email.php'; // Use your PHPMailer functions

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date("U") + 1800; // 30 minutes

            // Store in database
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires) VALUES (?, ?, ?)");
            $stmt->bind_param('isi', $user['id'], $token, $expires);
            $stmt->execute();

            // Create reset link
            $resetLink = "http://localhost/planit/reset_password.php?token=$token";
            $subject = "Password Reset Request";
            $message = "Hello {$user['username']},\n\nClick the link below to reset your password:\n$resetLink\n\nThis link expires in 30 minutes.";

            // Send email using PHPMailer
            if (sendEmail($email, $message, $subject)) {
                $success = "A password reset link has been sent to your email.";
            } else {
                $errors[] = "Failed to send email. Please try again later.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Forgot Password</h1>

  <?php if ($errors): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
      </ul>
    </div>
  <?php elseif ($success): ?>
    <div class="success"><?php echo $success; ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <label>Email</label>
    <input type="email" name="email" required>
    <button type="submit">Send Reset Link</button>
  </form>

  <p><a href="login.php">Back to Login</a></p>
</body>
</html>
