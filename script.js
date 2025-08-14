// document.querySelectorAll('.subtask-toggle').forEach(checkbox => {
//     checkbox.addEventListener('change', () => {
//     const subtaskId = checkbox.getAttribute('data-subtask-id');
//     const status = checkbox.checked ? 'completed' : 'pending';

//     fetch('toggle_subtask.php', {
//         method: 'POST',
//         headers: {'Content-Type': 'application/x-www-form-urlencoded'},
//         body: `id=${subtaskId}&status=${status}`
//     }).then(res => res.text())
//         .then(data => {
//         // Optionally show success or error messages here
//         // console.log(data);
//         }).catch(err => {
//         alert('Failed to update subtask status.');
//         });
//     });
// });

// function updateStatus(taskId, newStatus) {
//     fetch('update.php', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//         body: 'id=' + taskId + '&status=' + newStatus
//     })
//     .then(res => res.text())
//     .then(data => {
//         console.log(data); // Debugging
//         // alert('Status updated successfully');
//     })
//     .catch(err => console.error(err));
// }

// function updatePriority(taskId, newPriority) {
//     fetch('update.php', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//         body: 'id=' + taskId + '&priority=' + newPriority
//     })
//     .then(res => res.text())
//     .then(data => {
//         console.log(data); // Debugging
//         // alert('Priority updated successfully');
//     })
//     .catch(err => console.error(err));
// }

// function updateStatus(taskId, newStatus, rowElement) {
//     fetch('update.php', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//         body: `id=${taskId}&status=${encodeURIComponent(newStatus)}`
//     })
//     .then(response => response.text())
//     .then(() => {
//         const tbody = rowElement.parentElement;

//         if (newStatus === 'completed') {
//             rowElement.classList.add('completed-task');
//             // move above the first subtask row or at the end
//             let lastRow = Array.from(tbody.querySelectorAll('tr.task-row')).pop();
//             tbody.appendChild(rowElement);
//         } else {
//             rowElement.classList.remove('completed-task');

//             // get all task rows except this one and completed
//             let taskRows = Array.from(tbody.querySelectorAll('tr.task-row'))
//                 .filter(r => r !== rowElement && !r.classList.contains('completed-task'));

//             let rowDate = new Date(rowElement.cells[2].innerText);
//             let inserted = false;

//             for (let r of taskRows) {
//                 let rDate = new Date(r.cells[2].innerText);
//                 if (rowDate < rDate) {
//                     tbody.insertBefore(rowElement, r);
//                     inserted = true;
//                     break;
//                 }
//             }

//             if (!inserted) {
//                 // insert before first completed row
//                 let firstCompleted = tbody.querySelector('tr.completed-task');
//                 if (firstCompleted) {
//                     tbody.insertBefore(rowElement, firstCompleted);
//                 } else {
//                     tbody.appendChild(rowElement);
//                 }
//             }
//         }
//     });
// }

// Subtask toggle
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

// Subtask form toggle
document.querySelectorAll('.toggleSubtaskBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const nextRow = btn.closest('tr').nextElementSibling;
        if(nextRow && nextRow.classList.contains('subtask-row')){
            if(nextRow.style.display==='none'){
                nextRow.style.display='table-row';
                btn.textContent='Hide Subtask Form';
            } else {
                nextRow.style.display='none';
                btn.textContent='Add Subtask';
            }
        }
    });
});


const toggleBtn = document.getElementById('toggleSubtaskBtn');
  const formRow = document.getElementById('subtaskFormRow');

  toggleBtn.addEventListener('click', () => {
    if (formRow.style.display === 'none') {
      formRow.style.display = 'table-row';
      toggleBtn.textContent = 'Hide Subtask Form';
    } else {
      formRow.style.display = 'none';
      toggleBtn.textContent = 'Add Subtask';
    }
  });

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