function restoreNote(noteId) {
    fetch('api/restore_note.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ note_id: noteId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`[data-note-id="${noteId}"]`).remove();
            showNotification('Note restored successfully', 'success');
        } else {
            showNotification('Error restoring note', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error restoring note', 'error');
    });
}

function restoreReminder(reminderId) {
    fetch('api/restore_reminder.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reminder_id: reminderId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`[data-reminder-id="${reminderId}"]`).remove();
            showNotification('Reminder restored successfully', 'success');
        } else {
            showNotification('Error restoring reminder', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error restoring reminder', 'error');
    });
}

function permanentlyDeleteReminder(reminderId) {
    if (!confirm('Are you sure you want to permanently delete this reminder? This action cannot be undone.')) {
        return;
    }

    fetch('api/permanently_delete_reminder.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ reminder_id: reminderId })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const reminderElement = document.querySelector(`[data-reminder-id="${reminderId}"]`);
            if (reminderElement) {
                reminderElement.remove();
                showNotification('Reminder permanently deleted', 'success');
                
                // Check if trash is empty
                const remainingItems = document.querySelectorAll('[data-note-id], [data-reminder-id]');
                if (remainingItems.length === 0) {
                    location.reload();
                }
            }
        } else {
            showNotification(data.message || 'Error deleting reminder', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error deleting reminder', 'error');
    });
}

function permanentlyDeleteNote(noteId) {
    if (!confirm('Are you sure you want to permanently delete this note? This action cannot be undone.')) {
        return;
    }

    const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
    if (!noteElement) {
        console.error('Note element not found:', noteId);
        return;
    }

    // Add fade out animation
    noteElement.style.transition = 'all 0.3s ease';
    noteElement.style.transform = 'scale(0.9)';
    noteElement.style.opacity = '0';

    fetch('api/permanently_delete_note.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ note_id: noteId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            setTimeout(() => {
                noteElement.remove();
                showNotification('Note permanently deleted', 'success');
                
                // Check if trash is empty
                const remainingItems = document.querySelectorAll('[data-note-id], [data-reminder-id]');
                if (remainingItems.length === 0) {
                    location.reload();
                }
            }, 300);
        } else {
            // Revert animation if failed
            noteElement.style.transition = 'all 0.3s ease';
            noteElement.style.transform = '';
            noteElement.style.opacity = '';
            showNotification('Error deleting note: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Revert animation if failed
        noteElement.style.transition = 'all 0.3s ease';
        noteElement.style.transform = '';
        noteElement.style.opacity = '';
        showNotification('Error deleting note. Please try again.', 'error');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.id = 'notification';
    notification.classList.add('notification', 'fixed', 'bottom-4', 'inset-x-0', 'mx-auto', 'w-auto', 'max-w-md', 'px-4', 'py-3', 'rounded-lg', 'shadow-lg', 'z-50', 'flex', 'items-center', 'justify-center', 'space-x-2');
    
    if (type === 'error') {
        notification.classList.add('bg-red-100', 'dark:bg-red-900', 'border', 'border-red-400', 'dark:border-red-700', 'text-red-700', 'dark:text-red-200');
    } else {
        notification.classList.add('bg-green-100', 'dark:bg-green-900', 'border', 'border-green-400', 'dark:border-green-700', 'text-green-700', 'dark:text-green-200');
    }

    notification.innerHTML = `
        <i class="fas ${type === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>
        <span class="text-center">${message}</span>
        <button onclick="closeNotification()" class="ml-4 ${type === 'error' ? 'text-red-700 dark:text-red-200 hover:text-red-900 dark:hover:text-red-100' : 'text-green-700 dark:text-green-200 hover:text-green-900 dark:hover:text-green-100'}">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    setTimeout(() => {
        closeNotification();
    }, 3000);
}

function closeNotification() {
    const notification = document.getElementById('notification');
    if (notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

// Logout Modal functionality
function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    const modalContent = modal.querySelector('div > div');
    modal.classList.remove('hidden');
    // Small delay to ensure the display:flex is applied before the transform
    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    const modalContent = modal.querySelector('div > div');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

// Close modal when clicking outside
document.getElementById('logoutModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLogoutModal();
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('logoutModal').classList.contains('hidden')) {
        closeLogoutModal();
    }
});

// Empty Trash Functionality
function showEmptyTrashModal() {
    const modal = document.getElementById('emptyTrashModal');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.querySelector('div').classList.remove('scale-95', 'opacity-0');
    }, 10);
}

function closeEmptyTrashModal() {
    const modal = document.getElementById('emptyTrashModal');
    modal.querySelector('div').classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 200);
}

function emptyTrash() {
    fetch('api/empty_trash.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification('Error emptying trash', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error emptying trash', 'error');
    })
    .finally(() => {
        closeEmptyTrashModal();
    });
}

// Event Listeners
document.getElementById('emptyTrashBtn').addEventListener('click', showEmptyTrashModal);

// Close modal when clicking outside
document.getElementById('emptyTrashModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEmptyTrashModal();
    }
});