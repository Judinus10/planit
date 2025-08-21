document.querySelectorAll('.toggleSubtaskBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const taskRow = btn.closest('tr');
        const statusSelect = taskRow.querySelector('td select'); // Select the status dropdown
        const status = statusSelect ? statusSelect.value.toLowerCase() : '';
        let nextRow = taskRow.nextElementSibling;

        // Disable adding subtask if task is completed
        if (status === 'completed') {
            alert("Cannot add subtask to a completed task.");
            return;
        }

        while(nextRow && (nextRow.classList.contains('subtask-row') || nextRow.classList.contains('subtask-form-row'))) {
            if(nextRow.style.display === 'none' || nextRow.style.display === '') {
                nextRow.style.display = (nextRow.classList.contains('subtask-row') || nextRow.classList.contains('subtask-form-row')) ? 'table-row' : '';
            } else {
                nextRow.style.display = 'none';
            }
            nextRow = nextRow.nextElementSibling;
        }

        // Toggle button text based on first row's visibility
        const firstRow = taskRow.nextElementSibling;
        btn.textContent = (firstRow.style.display === 'table-row') ? 'Add Subtask' : 'Hide Subtask Form';
    });
});


// Subtask checkbox toggle for status
// document.querySelectorAll('.subtask-toggle').forEach(checkbox => {
//     checkbox.addEventListener('change', function() {
//         const subtaskId = this.dataset.subtaskId;
//         const status = this.checked ? 'completed' : 'To-do';

//         fetch('update_subtask.php', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//             body: `id=${subtaskId}&status=${status}`
//         })
//         .then(res => res.text())
//         .then(data => {
//             if (data !== 'success') {
//                 alert("Error updating subtask: " + data);
//             }
//         });
//     });
// });

document.querySelectorAll('.subtask-toggle').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const subtaskId = this.dataset.subtaskId;
        const status = this.checked ? 'completed' : 'pending';

        fetch('toggle_subtask.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${subtaskId}&status=${status}`
        })
        .then(res => res.text())
        .then(data => {
            if (data.trim() !== "OK") {
                alert("Failed to update subtask!");
                this.checked = !this.checked; // revert checkbox if failed
            }
        })
        .catch(err => {
            alert("Error updating subtask!");
            this.checked = !this.checked;
        });
    });
});

// Task status update + reorder
// function updateStatus(taskId, newStatus, rowElement) {
//     fetch('update.php', {
//         method:'POST',
//         headers:{'Content-Type':'application/x-www-form-urlencoded'},
//         body:`id=${taskId}&status=${encodeURIComponent(newStatus)}`
//     }).then(()=>{
//         const tbody = rowElement.parentElement;

//         if(newStatus==='completed') {
//             rowElement.classList.add('completed-task');
//             tbody.appendChild(rowElement);
//         } else {
//             rowElement.classList.remove('completed-task');

//             let taskRows = Array.from(tbody.querySelectorAll('tr.task-row'))
//                 .filter(r=>r!==rowElement && !r.classList.contains('completed-task'));

//             let rowDate = new Date(rowElement.cells[2].innerText);
//             let inserted=false;

//             for(let r of taskRows){
//                 let rDate = new Date(r.cells[2].innerText);
//                 if(rowDate < rDate){
//                     tbody.insertBefore(rowElement,r);
//                     inserted=true;
//                     break;
//                 }
//             }

//             if(!inserted){
//                 let firstCompleted = tbody.querySelector('tr.completed-task');
//                 if(firstCompleted){
//                     tbody.insertBefore(rowElement, firstCompleted);
//                 } else {
//                     tbody.appendChild(rowElement);
//                 }
//             }
//         }
//     });
// }

// ...existing code...
function updateStatus(taskId, newStatus, rowElement) {
    fetch('update.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`id=${taskId}&status=${encodeURIComponent(newStatus)}`
    }).then(()=>{
        const tbody = rowElement.parentElement;

        if(newStatus==='completed') {
            rowElement.classList.add('completed-task');
            tbody.appendChild(rowElement);

            // Hide subtask rows and subtask form row
            let nextRow = rowElement.nextElementSibling;
            while(nextRow && (nextRow.classList.contains('subtask-row') || nextRow.classList.contains('subtask-form-row'))) {
                nextRow.style.display = 'none';
                nextRow = nextRow.nextElementSibling;
            }
        } else {
            rowElement.classList.remove('completed-task');

            let taskRows = Array.from(tbody.querySelectorAll('tr.task-row'))
                .filter(r=>r!==rowElement && !r.classList.contains('completed-task'));

            let rowDate = new Date(rowElement.cells[2].innerText);
            let inserted=false;

            for(let r of taskRows){
                let rDate = new Date(r.cells[2].innerText);
                if(rowDate < rDate){
                    tbody.insertBefore(rowElement,r);
                    inserted=true;
                    break;
                }
            }

            if(!inserted){
                let firstCompleted = tbody.querySelector('tr.completed-task');
                if(firstCompleted){
                    tbody.insertBefore(rowElement, firstCompleted);
                } else {
                    tbody.appendChild(rowElement);
                }
            }

            // Show subtask rows and subtask form row
            let nextRow = rowElement.nextElementSibling;
            while(nextRow && (nextRow.classList.contains('subtask-row') || nextRow.classList.contains('subtask-form-row'))) {
                nextRow.style.display = 'table-row';
                nextRow = nextRow.nextElementSibling;
            }
        }
    });
}

document.querySelectorAll('.delete-subtask-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const subtaskId = btn.dataset.subtaskId;
        if(confirm('Delete this subtask?')) {
            fetch('delete_subtask.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `id=${subtaskId}`
            })
            .then(res => res.text())
            .then(() => {
                // Remove subtask row from DOM
                btn.closest('li').remove();
            })
            .catch(() => alert('Failed to delete subtask'));
        }
    });
});

// Task priority update
function updatePriority(taskId,newPriority){
    fetch('update.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`id=${taskId}&priority=${encodeURIComponent(newPriority)}`
    }).catch(err=>console.error(err));
}


// const toggleBtn = document.getElementById('toggleSubtaskBtn');
//   const formRow = document.getElementById('subtaskFormRow');

//   toggleBtn.addEventListener('click', () => {
//     if (formRow.style.display === 'none') {
//       formRow.style.display = 'table-row';
//       toggleBtn.textContent = 'Hide Subtask Form';
//     } else {
//       formRow.style.display = 'none';
//       toggleBtn.textContent = 'Add Subtask';
//     }
//   });