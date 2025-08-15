<?php
session_start();
require 'db.php';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    $error = '';

    // Check if email or password is being changed
    if ($email !== $user['email'] || !empty($new_password)) {
        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        }
    }

    // If no error, update details
    if (empty($error)) {
        $hashed_password = !empty($new_password) ? password_hash($new_password, PASSWORD_DEFAULT) : $user['password'];

        $update = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $update->bind_param("sssi", $username, $email, $hashed_password, $user_id);

        if ($update->execute()) {
            $_SESSION['username'] = $username;
            $success = "Profile updated successfully!";
            // Refresh user data
            $user['username'] = $username;
            $user['email'] = $email;
            $user['password'] = $hashed_password;
        } else {
            $error = "Failed to update profile.";
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

    <?php if (isset($success))
        echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error))
        echo "<p style='color:red;'>$error</p>"; ?>

    <form method="post" style="max-width:400px;">
        <label>Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

        <label>New Password:</label><br>
        <input type="password" name="new_password" placeholder="Leave blank to keep current password"><br><br>

        <label>Current Password (required if changing email or password):</label><br>
        <input type="password" name="current_password" placeholder="Enter current password"
            autocomplete="new-password"><br><br>


        <button type="submit">Save Changes</button>
    </form>
</body>

</html>