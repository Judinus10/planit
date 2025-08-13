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