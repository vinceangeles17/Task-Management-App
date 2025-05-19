function createNotification(message) {
    // Create notification container if it doesn't exist
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        container.style.display = 'flex';
        container.style.flexDirection = 'column';
        container.style.gap = '10px';
        document.body.appendChild(container);
    }

    // Create notification element
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.background = 'rgba(0, 119, 204, 0.9)';
    notification.style.color = 'white';
    notification.style.padding = '12px 20px';
    notification.style.borderRadius = '8px';
    notification.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.3)';
    notification.style.fontWeight = '600';
    notification.style.minWidth = '200px';
    notification.style.opacity = '1';
    notification.style.transition = 'opacity 0.5s ease';

    container.appendChild(notification);

    // Remove notification after 3 seconds with fade out
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            container.removeChild(notification);
            if (container.childElementCount === 0) {
                document.body.removeChild(container);
            }
        }, 500);
    }, 3000);
}

function notifyAddTask() {
    createNotification("Task added successfully.");
}

function notifyChangeStatus() {
    createNotification("Task status updated successfully.");
}

function notifyEditTask() {
    createNotification("Task edited successfully.");
}

function notifyDeleteTask() {
    createNotification("Task deleted successfully.");
}
