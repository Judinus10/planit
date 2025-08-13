<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch tasks for this user
$sql = "SELECT * FROM tasks WHERE user_id = $user_id ORDER BY due_date ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
  <title>My Tasks</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="top-right">
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="logout.php">Logout</a></p>
  </div>

  <h1>Your Tasks</h1>

  <a href="add_task.php">+ Add New Task</a>
  <a href="add_task.php">+ Add Member</a>

  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Due Date</th>
        <th>Status</th>
        <th>Priority</th>
        <th colspan="2" style="text-align: center;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="6" style="text-align:center; padding:20px;">No tasks yet.</td></tr>
      <?php else: ?>
        <?php while ($task = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($task['title']); ?></td>
            <td><?php echo htmlspecialchars($task['description']); ?></td>
            <td><?php echo htmlspecialchars($task['due_date']); ?></td>
            <td>
              <select onchange='updateStatus(<?php echo $task['id']; ?>, this.value)'>
                <option value="to-do" <?php echo ($task['status'] == 'to-do') ? 'to-do' : ''?>> To-do</option>
                <option value='on progress' <?php echo ($task["status"]== 'on progress')? 'selected' : ''?>>on progress</option>
                <option value="complete" <?php echo ($task["status"]== "completed" ) ? "completed" : '' ?>>Completed</option>
              </select>
            </td>
            <td><?php echo htmlspecialchars($task['priority']); ?></td>
            <td>
              <a href="edit_task.php?id=<?php echo $task['id']; ?>">Edit</a>
            </td>
            <td>
              <a href="delete_task.php?id=<?php echo $task['id']; ?>" onclick="return confirm('Delete this task?');">Delete</a>
            </td>
          </tr>

          <?php
          // Fetch subtasks for this task
          $task_id = $task['id'];
          $subtask_sql = "SELECT * FROM subtasks WHERE task_id = $task_id ORDER BY created_at ASC";
          $subtask_result = $conn->query($subtask_sql);

          if ($subtask_result->num_rows > 0):
          ?>
          <tr>
            <td colspan="7" style="padding-left: 40px; background: #f0eaff;">
              <strong>Subtasks:</strong>
              <ul style="margin-top: 8px;">
                <?php while ($subtask = $subtask_result->fetch_assoc()): 
                  $checked = ($subtask['status'] === 'completed') ? "checked" : "";
                ?>
                  <li>
                    <input type="checkbox" data-subtask-id="<?php echo $subtask['id']; ?>" class="subtask-toggle" <?php echo $checked; ?>>
                    <?php echo htmlspecialchars($subtask['title']); ?>
                    <a href="delete_subtask.php?id=<?php echo $subtask['id']; ?>&task_id=<?php echo $task_id; ?>" style="color:white; margin-left:8px;">Delete</a>
                  </li>
                <?php endwhile; ?>
              </ul>
            </td>
          </tr>
          <?php endif; ?>

          <tr>
            <td colspan="7" style="padding-left: 40px; background: #f0eaff;">
              <form method="POST" action="add_subtask.php" style="display: flex; gap: 8px; align-items: center;">
                <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                <input type="text" name="title" placeholder="Add new subtask..." required style="flex-grow: 1; padding: 6px 10px; border-radius: 4px; border: 1px solid #ccc;">
                <button type="submit" style="background:#7b2ff7; color:white; border:none; padding:6px 14px; border-radius:4px; cursor:pointer;">Add</button>
              </form>
            </td>
          </tr>

        <?php endwhile; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <script>
    document.querySelectorAll('.subtask-toggle').forEach(checkbox => {
      checkbox.addEventListener('change', () => {
        const subtaskId = checkbox.getAttribute('data-subtask-id');
        const status = checkbox.checked ? 'completed' : 'pending';

        fetch('toggle_subtask.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `id=${subtaskId}&status=${status}`
        }).then(res => res.text())
          .then(data => {
            // Optionally show success or error messages here
            // console.log(data);
          }).catch(err => {
            alert('Failed to update subtask status.');
          });
      });
    });
  </script>

</body>
</html>
