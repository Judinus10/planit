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