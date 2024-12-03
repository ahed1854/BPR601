document.addEventListener('DOMContentLoaded', function() {
    loadReminders();
    
    const reminderForm = document.getElementById('reminderForm');
    const titleInput = document.getElementById('title');
    const contentInput = document.getElementById('content');
    const dateInput = document.getElementById('reminderDate');

    // Clear any initial content and spaces
    titleInput.value = '';
    contentInput.value = '';
    dateInput.value = '';

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;

    reminderForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const title = titleInput.value.trim();
        const content = contentInput.value.trim();
        const reminderDate = dateInput.value;
        
        if (!content || !reminderDate) {
            showNotification('Please fill in the required fields', 'error');
            return;
        }

        createReminder(title, content, reminderDate);
        titleInput.value = '';
        contentInput.value = '';
        dateInput.value = '';
        contentInput.style.height = 'auto';
    });

    contentInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});

function createReminder(title, content, reminderDate) {
    fetch('api/reminders.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            title: title,
            content: content,
            reminderDate: reminderDate + ':00' // Ensure correct datetime format
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const remindersGrid = document.getElementById('remindersGrid');
            const reminderElement = createReminderElement(data.reminder);
            remindersGrid.insertBefore(reminderElement, remindersGrid.firstChild);
            showNotification('Reminder created successfully', 'success');
        } else {
            showNotification('Error creating reminder: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error creating reminder:', error);
        showNotification('Error creating reminder. Please try again.', 'error');
    });
}

function createReminderElement(reminder) {
    const div = document.createElement('div');
    div.className = 'reminder rounded-lg p-4';
    div.setAttribute('data-reminder-id', reminder.id);
    div.setAttribute('data-reminder-date', reminder.reminder_date);
    
    const date = new Date(reminder.reminder_date);
    const formattedDate = date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric',
    });

    div.innerHTML = `
        <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-medium">${escapeHtml(reminder.title || 'Untitled')}</h3>
            <button onclick="editReminder(${reminder.id})" class="p-1 rounded-full hover:bg-opacity-20">
                <i class="fas fa-pen"></i>
            </button>
        </div>
        <p>${escapeHtml(reminder.content)}</p>
        <div class="flex justify-between items-center mt-4">
            <div class="flex items-center text-sm">
                <i class="fas fa-clock mr-2"></i>
                ${formattedDate}
            </div>
            <button onclick="moveToTrash(${reminder.id})" class="p-2 rounded-full hover:bg-opacity-20">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    
    return div;
}

function editReminder(reminderId) {
    const reminderElement = document.querySelector(`[data-reminder-id="${reminderId}"]`);
    if (!reminderElement) return;

    const titleElement = reminderElement.querySelector('h3');
    const contentElement = reminderElement.querySelector('p');
    const dateElement = reminderElement.querySelector('.text-sm');
    
    const currentTitle = titleElement.textContent;
    const currentContent = contentElement.textContent;
    const currentDate = reminderElement.getAttribute('data-reminder-date');
    
    // Format the date for datetime-local input
    const dateObj = new Date(currentDate);
    const formattedDateTime = dateObj.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:mm

    reminderElement.innerHTML = `
        <form class="edit-reminder-form space-y-4">
            <input type="text" class="edit-title w-full p-2 rounded focus:outline-none text-lg" value="${escapeHtml(currentTitle)}">
            <textarea class="edit-content w-full min-h-[60px] p-2 rounded focus:outline-none resize-none">${escapeHtml(currentContent)}</textarea>
            <input type="datetime-local" class="edit-date w-full p-2 rounded focus:outline-none" value="${formattedDateTime}">
            <div class="flex justify-end space-x-2">
                <button type="submit" class="px-4 py-2 rounded">Save</button>
                <button type="button" class="cancel-edit px-4 py-2 rounded">Cancel</button>
            </div>
        </form>
    `;

    const form = reminderElement.querySelector('.edit-reminder-form');
    const cancelButton = reminderElement.querySelector('.cancel-edit');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const newTitle = this.querySelector('.edit-title').value.trim();
        const newContent = this.querySelector('.edit-content').value.trim();
        const newDate = this.querySelector('.edit-date').value;

        if (!newContent || !newDate) {
            showNotification('Content and date are required', 'error');
            return;
        }

        updateReminder(reminderId, newTitle, newContent, newDate);
    });

    cancelButton.addEventListener('click', function() {
        const date = new Date(currentDate);
        const formattedDate = date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        reminderElement.innerHTML = `
            <div class="flex justify-between items-start mb-2">
                <h3 class="text-lg font-medium">${escapeHtml(currentTitle)}</h3>
                <button onclick="editReminder(${reminderId})" class="p-1 rounded-full hover:bg-opacity-20">
                    <i class="fas fa-pen"></i>
                </button>
            </div>
            <p>${escapeHtml(currentContent)}</p>
            <div class="flex justify-between items-center mt-4">
                <div class="flex items-center text-sm">
                    <i class="fas fa-clock mr-2"></i>
                    ${formattedDate}
                </div>
                <button onclick="moveToTrash(${reminderId})" class="p-2 rounded-full hover:bg-opacity-20">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    });
}

function updateReminder(reminderId, title, content, reminderDate) {
    fetch('api/reminders.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            id: reminderId, 
            title: title, 
            content: content,
            reminderDate: reminderDate + ':00' // Ensure correct datetime format
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const reminderElement = document.querySelector(`[data-reminder-id="${reminderId}"]`);
            const date = new Date(reminderDate);
            const formattedDate = date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            reminderElement.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-lg font-medium">${escapeHtml(title || 'Untitled')}</h3>
                    <button onclick="editReminder(${reminderId})" class="p-1 rounded-full hover:bg-opacity-20">
                        <i class="fas fa-pen"></i>
                    </button>
                </div>
                <p>${escapeHtml(content)}</p>
                <div class="flex justify-between items-center mt-4">
                    <div class="flex items-center text-sm">
                        <i class="fas fa-clock mr-2"></i>
                        ${formattedDate}
                    </div>
                    <button onclick="moveToTrash(${reminderId})" class="p-2 rounded-full hover:bg-opacity-20">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            reminderElement.setAttribute('data-reminder-date', reminderDate);
            showNotification('Reminder updated successfully', 'success');
        } else {
            showNotification('Error updating reminder: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error updating reminder:', error);
        showNotification('Error updating reminder. Please try again.', 'error');
    });
}

function moveToTrash(reminderId) {
    const reminderElement = document.querySelector(`[data-reminder-id="${reminderId}"]`);
    if (!reminderElement) return;

    reminderElement.style.transition = 'all 0.3s ease';
    reminderElement.style.transform = 'scale(0.9)';
    reminderElement.style.opacity = '0';

    fetch('api/move_to_trash.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: reminderId, type: 'reminder' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            setTimeout(() => {
                reminderElement.remove();
                showNotification('Reminder moved to trash', 'success');
            }, 300);
        } else {
            showNotification('Error moving reminder to trash', 'error');
            reminderElement.style.transform = '';
            reminderElement.style.opacity = '';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error moving reminder to trash. Please try again.', 'error');
        reminderElement.style.transform = '';
        reminderElement.style.opacity = '';
    });
}

function loadReminders() {
    fetch('api/reminders.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const remindersGrid = document.getElementById('remindersGrid');
            remindersGrid.innerHTML = '';
            data.reminders.forEach(reminder => {
                const reminderElement = createReminderElement(reminder);
                remindersGrid.appendChild(reminderElement);
            });
        } else {
            showNotification('Error loading reminders: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        console.error('Error loading reminders:', error);
        showNotification('Error loading reminders. Please try again.', 'error');
    });
}

function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    if (!notification) return;

    notification.textContent = message;
    notification.className = `notification fixed bottom-4 inset-x-0 mx-auto w-auto max-w-md px-4 py-3 rounded-lg shadow-lg z-50 ${
        type === 'error' 
            ? 'bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200' 
            : 'bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200'
    }`;
    
    notification.classList.add('show');
    setTimeout(closeNotification, 3000);
}

function closeNotification() {
    const notification = document.getElementById('notification');
    if (!notification) return;
    
    notification.classList.remove('show');
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

