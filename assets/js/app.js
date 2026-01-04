/**
 * JavaScript for Live CCTV Manager
 * Handles all frontend interactions and API calls
 */

// Configuration
const API_BASE = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/')) + '/api/cameras.php';
const BASE_URL = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));

/**
 * Copy stream URL to clipboard
 */
function copyStreamUrl(streamKey) {
    const url = `${BASE_URL}/view.php?key=${streamKey}`;
    copyToClipboard(url, 'Stream URL copied to clipboard!');
}

/**
 * Copy share URL to clipboard
 */
function copyShareUrl() {
    const input = document.getElementById('shareUrl');
    if (input) {
        copyToClipboard(input.value, 'Share URL copied to clipboard!');
    }
}

/**
 * Generic copy to clipboard function
 */
function copyToClipboard(text, message) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showNotification(message, 'success');
        }).catch(err => {
            console.error('Failed to copy:', err);
            fallbackCopy(text, message);
        });
    } else {
        fallbackCopy(text, message);
    }
}

/**
 * Fallback copy method for older browsers
 */
function fallbackCopy(text, message) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        showNotification(message, 'success');
    } catch (err) {
        showNotification('Failed to copy to clipboard', 'error');
    }
    
    document.body.removeChild(textarea);
}

/**
 * Open RTSP stream in VLC
 */
function openInVLC(rtspUrl) {
    // Create VLC protocol link
    const vlcUrl = rtspUrl;
    
    // Show modal with instructions
    showNotification(
        'Copy the RTSP URL and paste it in VLC: Media > Open Network Stream',
        'info',
        5000
    );
    
    // Also copy to clipboard
    copyToClipboard(rtspUrl, 'RTSP URL copied! Paste it in VLC.');
}

/**
 * Toggle fullscreen mode
 */
function toggleFullscreen() {
    const container = document.getElementById('streamContainer');
    
    if (!container) return;
    
    if (!document.fullscreenElement) {
        container.requestFullscreen().catch(err => {
            showNotification('Failed to enter fullscreen', 'error');
        });
    } else {
        document.exitFullscreen();
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Remove existing notifications
    const existing = document.getElementById('notification');
    if (existing) {
        existing.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.id = 'notification';
    notification.className = 'fixed top-4 right-4 z-50 max-w-sm animate-fade-in';
    
    const bgColor = {
        'success': 'bg-green-600',
        'error': 'bg-red-600',
        'info': 'bg-blue-600',
        'warning': 'bg-yellow-600'
    }[type] || 'bg-gray-600';
    
    const icon = {
        'success': 'fa-check-circle',
        'error': 'fa-exclamation-circle',
        'info': 'fa-info-circle',
        'warning': 'fa-exclamation-triangle'
    }[type] || 'fa-bell';
    
    notification.innerHTML = `
        <div class="glass ${bgColor} rounded-lg p-4 shadow-lg">
            <div class="flex items-center space-x-3">
                <i class="fas ${icon} text-xl"></i>
                <p class="flex-1">${message}</p>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white/80 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after duration
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, duration);
}

// ============= ADMIN PANEL FUNCTIONS =============

/**
 * Open add camera modal
 */
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Camera';
    document.getElementById('cameraForm').reset();
    document.getElementById('cameraId').value = '';
    document.getElementById('cameraModal').classList.remove('hidden');
}

/**
 * Edit camera
 */
function editCamera(camera) {
    document.getElementById('modalTitle').textContent = 'Edit Camera';
    document.getElementById('cameraId').value = camera.id;
    document.getElementById('cameraName').value = camera.name;
    document.getElementById('cameraLocation').value = camera.location || '';
    document.getElementById('cameraRtsp').value = camera.rtsp_url;
    document.getElementById('cameraStatus').value = camera.status;
    document.getElementById('cameraModal').classList.remove('hidden');
}

/**
 * Close modal
 */
function closeModal() {
    document.getElementById('cameraModal').classList.add('hidden');
    document.getElementById('cameraForm').reset();
}

/**
 * Handle form submission
 */
if (document.getElementById('cameraForm')) {
    document.getElementById('cameraForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        const isEdit = !!data.id;
        
        try {
            const response = await fetch(API_BASE, {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification(result.message, 'success');
                closeModal();
                
                // Reload page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            showNotification('Failed to save camera: ' + error.message, 'error');
        }
    });
}

/**
 * Toggle camera status
 */
async function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    
    try {
        const response = await fetch(API_BASE, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: id,
                status: newStatus
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Camera status updated', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Failed to update status: ' + error.message, 'error');
    }
}

/**
 * Delete camera
 */
async function deleteCamera(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
        return;
    }
    
    try {
        const response = await fetch(API_BASE + '?id=' + id, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Camera deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Failed to delete camera: ' + error.message, 'error');
    }
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    const modal = document.getElementById('cameraModal');
    if (modal && e.target === modal) {
        closeModal();
    }
});

// Close modal with Escape key
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('Live CCTV Manager initialized');
});
