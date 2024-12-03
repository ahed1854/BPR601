document.addEventListener('DOMContentLoaded', function() {
    loadNotes();
    
    const noteForm = document.getElementById('noteForm');
    const titleInput = document.getElementById('noteTitle');
    const contentInput = document.getElementById('noteContent');

    // Clear any initial content and spaces
    titleInput.value = '';
    contentInput.value = '';

    noteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const title = titleInput.value.trim();
        const content = contentInput.value.trim();
        
        if (content) {
            createNote(title, content);
            titleInput.value = '';
            contentInput.value = '';
            contentInput.style.height = 'auto';
        }
    });

    contentInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});

function createNote(title, content) {
    fetch('api/notes.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ title, content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notesGrid = document.getElementById('notesGrid');
            const noteElement = createNoteElement(data.note);
            notesGrid.insertBefore(noteElement, notesGrid.firstChild);
            showNotification('Note created successfully', 'success');
        } else {
            showNotification('Error creating note: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error creating note:', error);
        showNotification('Error creating note. Please try again.', 'error');
    });
}

function createNoteElement(note) {
    const div = document.createElement('div');
    div.className = 'note rounded-lg p-4';
    div.setAttribute('data-note-id', note.id);
    
    div.innerHTML = `
        <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-medium">${escapeHtml(note.title)}</h3>
            <button onclick="editNote(${note.id})" class="p-1 rounded-full hover:bg-opacity-20">
                <i class="fas fa-pen"></i>
            </button>
        </div>
        <p>${escapeHtml(note.content)}</p>
        <div class="flex justify-end mt-4 space-x-2">
            <button onclick="deleteNote(${note.id})" class="p-2 rounded-full hover:bg-opacity-20">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    return div;
}

function editNote(noteId) {
    const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
    if (!noteElement) return;

    const titleElement = noteElement.querySelector('h3');
    const contentElement = noteElement.querySelector('p');
    const currentTitle = titleElement.textContent;
    const currentContent = contentElement.textContent;

    noteElement.innerHTML = `
        <form class="edit-note-form">
            <input type="text" class="edit-title w-full p-2 bg-transparent focus:outline-none text-lg mb-2" value="${escapeHtml(currentTitle)}">
            <textarea class="edit-content w-full min-h-[20px] p-2 bg-transparent focus:outline-none resize-none">${escapeHtml(currentContent)}</textarea>
            <div class="flex justify-end mt-2">
                <button type="submit" class="px-4 py-1 text-sm rounded mr-2">Save</button>
                <button type="button" class="cancel-edit px-4 py-1 text-sm rounded">Cancel</button>
            </div>
        </form>
    `;

    const form = noteElement.querySelector('.edit-note-form');
    const cancelButton = noteElement.querySelector('.cancel-edit');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const newTitle = this.querySelector('.edit-title').value.trim();
        const newContent = this.querySelector('.edit-content').value.trim();
        updateNote(noteId, newTitle, newContent);
    });

    cancelButton.addEventListener('click', function() {
        noteElement.innerHTML = `
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-lg font-medium">${escapeHtml(currentTitle)}</h3>
                <button onclick="editNote(${noteId})" class="p-1 rounded-full hover:bg-opacity-20">
                    <i class="fas fa-pen"></i>
                </button>
            </div>
            <p>${escapeHtml(currentContent)}</p>
            <div class="flex justify-end mt-4 space-x-2">
                <button onclick="deleteNote(${noteId})" class="p-2 rounded-full hover:bg-opacity-20">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
}

function updateNote(noteId, title, content) {
    fetch('api/update_note.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: noteId, title, content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
            noteElement.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-lg font-medium">${escapeHtml(title)}</h3>
                    <button onclick="editNote(${noteId})" class="p-1 rounded-full hover:bg-opacity-20">
                        <i class="fas fa-pen"></i>
                    </button>
                </div>
                <p>${escapeHtml(content)}</p>
                <div class="flex justify-end mt-4 space-x-2">
                    <button onclick="deleteNote(${noteId})" class="p-2 rounded-full hover:bg-opacity-20">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            showNotification('Note updated successfully', 'success');
        } else {
            showNotification('Error updating note: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error updating note:', error);
        showNotification('Error updating note. Please try again.', 'error');
    });
}

function deleteNote(noteId) {
    const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
    if (!noteElement) return;

    noteElement.style.transition = 'all 0.3s ease';
    noteElement.style.transform = 'scale(0.9)';
    noteElement.style.opacity = '0';

    fetch('api/trash_note.php', {
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
                showNotification('Note moved to trash', 'success');
            }, 300);
        } else {
            showNotification('Error moving note to trash', 'error');
            noteElement.style.transform = '';
            noteElement.style.opacity = '';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error moving note to trash', 'error');
        noteElement.style.transform = '';
        noteElement.style.opacity = '';
    });
}

function loadNotes() {
    fetch('api/notes.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notesGrid = document.getElementById('notesGrid');
                notesGrid.innerHTML = '';
                data.notes.forEach(note => {
                    const noteElement = createNoteElement(note);
                    notesGrid.appendChild(noteElement);
                });
            } else {
                showNotification('Error loading notes: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading notes:', error);
            showNotification('Error loading notes. Please try again.', 'error');
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

function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}

