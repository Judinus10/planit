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
document.querySelectorAll('.subtask-toggle').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        const subtaskId = checkbox.dataset.subtaskId;
        const status = checkbox.checked ? 'completed' : 'pending';

        fetch('toggle_subtask.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `id=${subtaskId}&status=${status}`
        }).catch(() => alert('Failed to update subtask'));
    });
});


// Task status update + reorder
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
        }
    });
}

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