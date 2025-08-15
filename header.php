<!-- not in use need to be advance -->
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<div class="navbar">
    <!-- Left side -->
    <div class="nav-left">
        <a href="javascript:history.back()" class="back-button">&#8592; Back</a>
    </div>

    <!-- Center links -->
    <div class="nav-center">
        <a href="notification.php">Notifications</a>
        <a href="add_task.php?project_id=<?php echo $project_id ?? 0; ?>">+ Add Task</a>
        <a href="collab.php?project_id=<?php echo $project_id ?? 0; ?>">+ Add Member</a>
    </div>

    <!-- Right side -->
    <div class="nav-right">
        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> |
        <a href="edit_profile.php" title="Edit Profile">&#9998;</a> | <!-- pencil icon -->
        <a href="logout.php">Logout</a>
    </div>
</div>

<style>
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f5f5f5;
        padding: 10px 20px;
        font-family: Arial, sans-serif;
    }

    .nav-left a,
    .nav-center a,
    .nav-right a {
        text-decoration: none;
        color: #333;
        margin-right: 15px;
        font-weight: 500;
    }

    .nav-center a:last-child {
        margin-right: 0;
    }

    .nav-left,
    .nav-center,
    .nav-right {
        display: flex;
        align-items: center;
    }

    .nav-right a:hover {
        color: #6a0dad;
        /* violet hover */
    }
</style>