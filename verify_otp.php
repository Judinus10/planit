<?php
session_start();
require 'db.php';
require 'send_email.php'; // Make sure you have a sendOtpEmail($email, $otp) function

$errors = [];
$success_msg = "";

// Check required session variables
if (!isset($_SESSION['otp'], $_SESSION['otp_time'], $_SESSION['reg_username'], $_SESSION['reg_email'], $_SESSION['reg_password'])) {
    echo "No registration in progress.";
    exit;
}

$otp = $_SESSION['otp'];
$otp_time = $_SESSION['otp_time'];
$username = $_SESSION['reg_username'];
$email = $_SESSION['reg_email'];
$password_hash = $_SESSION['reg_password'];

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $user_otp = trim($_POST['otp']);

    if (time() - $otp_time > 300) {
        $errors[] = "OTP expired. Please request a new OTP.";
        session_destroy();
    } elseif ($user_otp == $otp) {
        // OTP correct → insert user into DB
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, email_verified) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $username, $email, $password_hash);

        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            $success_msg = "Registration successful! You can now <a href='login.php'>login</a>.";
        } else {
            $errors[] = "Failed to register user: " . $conn->error;
        }
    } else {
        $errors[] = "Invalid OTP. Please try again.";
    }
}

// Handle resend OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    $new_otp = rand(100000, 999999);
    $_SESSION['otp'] = $new_otp;
    $_SESSION['otp_time'] = time();

    if (sendOtpEmail($email, $new_otp)) {
        $errors[] = "A new OTP has been sent to your email.";
    } else {
        $errors[] = "Failed to resend OTP. Please try again.";
    }
}

// Timer
$time_left = max(0, 300 - (time() - $_SESSION['otp_time']));
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
            Time left: <?php echo sprintf("%02d:%02d", floor($time_left/60), $time_left%60); ?>
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
                if(timeLeft <= 0) {
                    document.getElementById('timer').innerHTML = "OTP expired. Please resend OTP.";
                    const verifyBtn = document.querySelector('button[name="verify"]');
                    if(verifyBtn) verifyBtn.disabled = true;
                    return;
                }
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                document.getElementById('timer').innerHTML = `Time left: ${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}`;
                timeLeft--;
            }
            setInterval(updateTimer, 1000);
        </script>
    <?php endif; ?>
</body>
</html>
