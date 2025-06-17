// Notification badge updater
document.addEventListener('DOMContentLoaded', function() {
    // Check if the user has approval access (check if the Approvals link exists)
    const approvalsLink = document.querySelector('a[href*="approvals.index"]');
    if (!approvalsLink) return;

    // Log only in debug mode
    if (window.debugMode) {
        console.log('Notification badge updater initialized');
    }
    
    // Function to update notification badge
    function updateNotificationBadge() {
        if (window.debugMode) {
            console.log('Fetching notification count...');
        }
        fetch('/notifications/count')
            .then(response => response.json())
            .then(data => {
                const count = data.count;
                if (window.debugMode) {
                    console.log('Notification count:', count);
                }
                
                // Only update the badge in the Approvals menu
                const badge = approvalsLink.querySelector('.notification-badge');
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            })
            .catch(error => console.error('Error fetching notification count:', error));
    }
    
    // Update immediately and then periodically
    updateNotificationBadge();
    
    // Update every 30 seconds
    setInterval(updateNotificationBadge, 30000);
});
