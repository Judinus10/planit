<!-- header.php -->
<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<div class="header">
    <a href="javascript:history.back()" class="back-button">&#8592; Back</a>
    <div class="top-right">
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="logout.php">Logout</a></p>
    </div>
</div>

<style>
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    background: #f5f5f5; /* optional style */
}

.back-button {
    text-decoration: none;
    font-size: 18px;
}

.top-right p {
    margin: 0;
}
</style>
