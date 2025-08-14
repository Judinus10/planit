<?php
session_start();
require 'db.php';
require 'send_email.php'; // Include your PHPMailer setup here

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  // Basic validations
  if (empty($username))
    $errors[] = "Username is required";
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = "Valid email is required";
  if (empty($password))
    $errors[] = "Password is required";
  if ($password !== $confirm_password)
    $errors[] = "Passwords do not match";

  // Check if username or email exists
  if (empty($errors)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $errors[] = "Username or email already taken";
    } else {
      // Hash password (we will save after OTP verification)
      $password_hash = password_hash($password, PASSWORD_DEFAULT);

      // Generate OTP
      $otp = rand(100000, 999999);

      // Save registration data + OTP in session
      $_SESSION['reg_username'] = $username;
      $_SESSION['reg_email'] = $email;
      $_SESSION['reg_password'] = $password_hash;
      $_SESSION['otp'] = $otp;
      $_SESSION['otp_time'] = time();

      // Send OTP using PHPMailer from send_otp.php
      $sent = sendOtpEmail($email, $otp); // A function you create in send_otp.php

      if ($sent) {
        header("Location: verify_otp.php");
        exit;
      } else {
        $errors[] = "Failed to send OTP email. Please try again.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Sign Up</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <a href="javascript:history.back()" class="back-button">
    &#8592; Back
  </a>
  <h1>Sign Up</h1>
  <?php if ($errors): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $e)
          echo "<li>$e</li>"; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="POST" action="">
    <label>Username</label>
    <input type="text" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">

    <label>Email</label>
    <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Confirm Password</label>
    <input type="password" name="confirm_password" required>

    <button type="submit">Sign Up</button>
  </form>
  <p>Already have an account? <a href="login.php">Log in here</a></p>
</body>

</html>