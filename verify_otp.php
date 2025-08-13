<?php
session_start();
require 'db.php';
require 'send_email.php'; // PHPMailer function sendOtpEmail()

$errors = [];

// Make sure a registration is in progress
if (!isset($_SESSION['otp'], $_SESSION['reg_email'])) {
    echo "No registration in progress.";
    exit;
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
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

        $stmt = $conn->prepare(
            "INSERT INTO users (username, email, password, email_verified) VALUES (?, ?, ?, 1)"
        );
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

// Handle resend OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time();

    if (sendOtpEmail($_SESSION['reg_email'], $otp)) {
        $errors[] = "A new OTP has been sent to your email.";
    } else {
        $errors[] = "Failed to resend OTP. Please try again.";
    }
}

// Calculate remaining time for timer
$time_left = 300 - (time() - $_SESSION['otp_time']);
$time_left = max($time_left, 0);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
    <style>
        .timer { font-weight: bold; color: red; margin-bottom: 10px; }
        .errors { color: red; }
    </style>
</head>
<body>
<h1>Verify OTP</h1>

<?php if ($errors): ?>
<div class="errors">
    <ul>
        <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
    </ul>
</div>
<?php endif; ?>

<p class="timer" id="timer">Time left: <?php echo sprintf("%02d:%02d", floor($time_left / 60), $time_left % 60); ?></p>

<form method="POST" action="">
    <label>Enter OTP sent to your email:</label>
    <input type="text" name="otp" maxlength="6">
    <button type="submit" name="verify">Verify</button>
    <button type="submit" name="resend">Resend OTP</button>
</form>

<script>
// Countdown timer in JavaScript
let timeLeft = <?php echo $time_left; ?>;

function updateTimer() {
    if (timeLeft <= 0) {
        document.getElementById('timer').innerHTML = "OTP expired. Please resend OTP.";
        document.querySelector('button[name="verify"]').disabled = true;
        return;
    }

    let minutes = Math.floor(timeLeft / 60);
    let seconds = timeLeft % 60;
    document.getElementById('timer').innerHTML = `Time left: ${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}`;
    timeLeft--;
}

setInterval(updateTimer, 1000);
</script>
</body>
</html>
