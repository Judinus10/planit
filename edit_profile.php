<?php
session_start();
require 'db.php';
require 'send_email.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user details
$stmt = $conn->prepare("SELECT username, email, password FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Determine if email or password is changing
    $email_changed = ($email !== $user['email']);
    $password_changed = !empty($new_password);

    // If email or password is changing, verify current password
    if (($email_changed || $password_changed) && !password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    }

    if (empty($error)) {
        $hashed_password = $password_changed ? password_hash($new_password, PASSWORD_DEFAULT) : $user['password'];

        // Email changed → trigger OTP verification
        if ($email_changed) {
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_time'] = time();
            $_SESSION['otp_mode'] = 'email_change';
            $_SESSION['new_email'] = $email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['hashed_password'] = $hashed_password;

            // Send OTP
            $subject = "Verify Your New Email";
            $message = "Hello {$username},\n\nYour OTP to verify your new email is: {$otp}\n\n- Task Manager";
            sendEmail($email, $message, $subject);

            header("Location: verify_otp.php");
            exit;
        } else {
            // Update username/password immediately
            $update = $conn->prepare("UPDATE users SET username=?, password=? WHERE id=?");
            $update->bind_param("ssi", $username, $hashed_password, $user_id);
            if ($update->execute()) {
                $_SESSION['username'] = $username;
                $success = "Profile updated successfully!";
                // Refresh user data
                $user['username'] = $username;
                $user['password'] = $hashed_password;
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<h1>Edit Profile</h1>

<?php if ($success) echo "<p style='color:green;'>$success</p>"; ?>
<?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>

<form method="post" style="max-width:400px;">
    <label>Username:</label><br>
    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

    <label>New Password:</label><br>
    <input type="password" name="new_password" placeholder="Leave blank to keep current password" autocomplete="new-password"><br><br>

    <label>Current Password (required if changing email or password):</label><br>
    <input type="password" name="current_password" placeholder="Enter current password" autocomplete="new-password"><br><br>

    <button type="submit">Save Changes</button>
</form>
</body>
</html>
