<?php
session_start();
require 'db.php';
require 'send_email.php';

$errors = [];
$success_msg = "";

// Ensure OTP session exists
if (!isset($_SESSION['otp'], $_SESSION['otp_mode'])) {
    echo "No action in progress.";
    exit;
}

$otp = $_SESSION['otp'];
$otp_mode = $_SESSION['otp_mode'];
$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? '';
$email = $_SESSION['new_email'] ?? ($_SESSION['email'] ?? '');
$hashed_password = $_SESSION['hashed_password'] ?? '';

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $user_otp = trim($_POST['otp']);

    if (!isset($_SESSION['otp_time'])) {
        $errors[] = "OTP session expired. Please request a new OTP.";
    } elseif (time() - $_SESSION['otp_time'] > 300) {
        $errors[] = "OTP expired. Please try again.";
        session_destroy();
    } elseif ($user_otp == $otp) {
        if ($otp_mode === 'registration') {
            // Insert new user and mark email verified
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, email_verified) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                session_unset();
                session_destroy();
                $success_msg = " Registration successful! <a href='login.php'>Login here</a>.";
            } else {
                $errors[] = "Failed to create account: " . $conn->error;
            }

        } elseif ($otp_mode === 'email_change') {
            // Update existing user's email
            $stmt = $conn->prepare("UPDATE users SET email=?, username=?, password=? WHERE id=?");
            $stmt->bind_param("sssi", $email, $username, $hashed_password, $user_id);

            if ($stmt->execute()) {
                session_unset();
                session_destroy();
                $success_msg = "✅ Details updated successfully! <a href='edit_profile.php'>Go back</a>.";
            } else {
                $errors[] = "Failed to update profile: " . $conn->error;
            }
        }
    } else {
        $errors[] = "Invalid OTP, please try again.";
    }
}

// Handle resend OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    $new_otp = rand(100000, 999999);
    $_SESSION['otp'] = $new_otp;
    $_SESSION['otp_time'] = time();

    if (sendEmail($email, "Hello {$username},\n\nYour OTP is: {$new_otp}\n\n- Task Manager", "Verify Your Email")) {
        $errors[] = "A new OTP has been sent to your email.";
    } else {
        $errors[] = "Failed to resend OTP. Please try again.";
    }
}

// Timer
$time_left = isset($_SESSION['otp_time']) ? max(0, 300 - (time() - $_SESSION['otp_time'])) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="javascript:history.back()" class="back-button">&#8592; Back</a>
    <h1>Verify OTP</h1>

    <?php if (!empty($success_msg)): ?>
        <div class="success">
            <p><?php echo $success_msg; ?></p>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (empty($success_msg)): ?>
        <p class="timer" id="timer">
            Time left: <?php echo sprintf("%02d:%02d", floor($time_left / 60), $time_left % 60); ?>
        </p>

        <form method="POST" action="">
            <label>Enter OTP sent to your email:</label>
            <input type="text" name="otp" maxlength="6" required>
            <button type="submit" name="verify">Verify</button>
            <button type="submit" name="resend">Resend OTP</button>
        </form>

        <script>
            let timeLeft = <?php echo $time_left; ?>;
            function updateTimer() {
                if (timeLeft <= 0) {
                    document.getElementById('timer').innerHTML = "OTP expired. Please resend OTP.";
                    const verifyBtn = document.querySelector('button[name="verify"]');
                    if (verifyBtn) verifyBtn.disabled = true;
                    return;
                }
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                document.getElementById('timer').innerHTML = `Time left: ${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                timeLeft--;
            }
            setInterval(updateTimer, 1000);
        </script>
    <?php endif; ?>
</body>
</html>
