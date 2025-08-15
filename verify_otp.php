<?php
session_start();
require 'db.php';
require 'send_email.php';

$errors = [];
$success = '';

// Check if OTP process is active
if (!isset($_SESSION['otp'], $_SESSION['otp_mode'])) {
    echo "No OTP process in progress.";
    exit;
}

// Extract common session data
$otp = $_SESSION['otp'];
$otp_time = $_SESSION['otp_time'];
$mode = $_SESSION['otp_mode']; // 'registration' or 'email_change'

// For email change mode
$new_email = $_SESSION['new_email'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? '';
$hashed_password = $_SESSION['hashed_password'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $user_otp = trim($_POST['otp']);

    // Check expiry (5 min)
    if (time() - $otp_time > 300) {
        $errors[] = "OTP expired. Please try again.";
        session_unset();
        session_destroy();
    } elseif ($user_otp == $otp) {
        if ($mode === 'registration') {
            // Registration flow
            $reg_username = $_SESSION['reg_username'];
            $reg_email = $_SESSION['reg_email'];
            $reg_password = $_SESSION['reg_password'];

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, email_verified) VALUES (?, ?, ?, 1)");
            $stmt->bind_param('sss', $reg_username, $reg_email, $reg_password);

            if ($stmt->execute()) {
                session_unset();
                session_destroy();
                $success = "Registration successful! You can <a href='login.php'>login</a> now.";
            } else {
                $errors[] = "Error creating user: " . $conn->error;
            }
        } elseif ($mode === 'email_change') {
            // Email change flow
            $update = $conn->prepare("UPDATE users SET email=?, username=?, password=? WHERE id=?");
            $update->bind_param("sssi", $new_email, $username, $hashed_password, $user_id);

            if ($update->execute()) {
                $_SESSION['username'] = $username;
                session_unset();
                session_destroy();
                $success = "Email verified and profile updated! Go back to <a href='edit_profile.php'>Edit Profile</a>";
            } else {
                $errors[] = "Failed to update profile.";
            }
        }
    } else {
        $errors[] = "Invalid OTP. Please try again.";
    }
}

// Handle resend OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_time'] = time();

    $email_to = $mode === 'registration' ? $_SESSION['reg_email'] : $_SESSION['new_email'];
    if (send_email($email_to, "Your OTP Code", "Hello, your OTP is: $otp")) {
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="javascript:history.back()" class="back-button">&#8592; Back</a>
<h1>Verify OTP</h1>

<?php if ($errors): ?>
    <div class="errors">
        <ul>
            <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
    </div>
<?php endif; ?>

<p class="timer" id="timer">
    Time left: <?php echo sprintf("%02d:%02d", floor($time_left / 60), $time_left % 60); ?>
</p>

<form method="POST">
    <label>Enter OTP sent to your email:</label><br>
    <input type="text" name="otp" maxlength="6" required autocomplete="off"><br><br>
    <button type="submit" name="verify">Verify</button>
    <button type="submit" name="resend">Resend OTP</button>
</form>

<?php if ($success): ?>
    <div class="success-message"><?php echo $success; ?></div>
<?php endif; ?>

<script>
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
