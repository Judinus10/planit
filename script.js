// index.php
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

function updateStatus(taskId, newStatus) {
    fetch('update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + taskId + '&status=' + newStatus
    })
    .then(res => res.text())
    .then(data => {
        console.log(data); // Debugging
        // alert('Status updated successfully');
    })
    .catch(err => console.error(err));
}

function updatePriority(taskId, newPriority) {
    fetch('update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + taskId + '&priority=' + newPriority
    })
    .then(res => res.text())
    .then(data => {
        console.log(data); // Debugging
        // alert('Priority updated successfully');
    })
    .catch(err => console.error(err));
}

// Countdown timer in JavaScript
// let timeLeft = <?php echo $time_left; ?>;

function updateTimer() {
    if (timeLeft <= 0) {
        document.getElementById('timer').innerHTML = "OTP expired. Please register again.";
        document.querySelector('button[type="submit"]').disabled = true;
        return;
    }

    let minutes = Math.floor(timeLeft / 60);
    let seconds = timeLeft % 60;
    document.getElementById('timer').innerHTML = `Time left: ${minutes.toString().padStart(2,'0')}:${seconds.toString().padStart(2,'0')}`;
    timeLeft--;
}

setInterval(updateTimer, 1000);