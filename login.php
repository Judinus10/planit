<?php
session_start();
require 'db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Password is correct
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;
                header("Location: project.php");
                exit;
            } else {
                $errors[] = "Incorrect password";
            }
        } else {
            $errors[] = "User not found";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Log In</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>Log In</h1>
  <?php if ($errors): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
      </ul>
    </div>
  <?php elseif (isset($_GET['signup'])): ?>
    <div class="success">Signup successful! You can log in now.</div>
  <?php endif; ?>
  <form method="POST" action="" id="login">
    <label>Username</label>
    <input type="text" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Log In</button>
  </form>
  <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
</body>
</html>
