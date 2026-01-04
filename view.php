<?php
/**
 * View Stream Page
 * Dedicated page for viewing a specific camera stream
 */

$pageTitle = 'View Stream - Live CCTV Manager';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/stream-helper.php';

// Get stream key from URL
$streamKey = $_GET['key'] ?? '';

if (empty($streamKey)) {
    header('Location: index.php');
    exit;
}

// Fetch camera details
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM cameras WHERE stream_key = ? AND status = 'active'");
    $stmt->execute([$streamKey]);
    $camera = $stmt->fetch();
    
    if (!$camera) {
        $notFound = true;
    } else {
        // Get MediaMTX URLs
        $hlsUrl = generateHlsUrl($streamKey);
        $mediamtxEnabled = isMediaMTXEnabled();
        $mediamtxStatus = getMediaMTXStatus();
    }
} catch (PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>

<?php if (isset($notFound)): ?>
    <!-- Camera Not Found -->
    <div class="max-w-2xl mx-auto text-center animate-fade-in">
        <div class="glass rounded-2xl p-12">
            <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
            <h2 class="text-3xl font-bold mb-4">Camera Not Found</h2>
            <p class="text-gray-400 mb-6">The camera stream you're looking for doesn't exist or is not active.</p>
            <a href="<?php echo $baseUrl; ?>/index.php" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                <i class="fas fa-home mr-2"></i>Back to Home
            </a>
        </div>
    </div>
<?php elseif (isset($error)): ?>
    <!-- Error Message -->
    <div class="glass border-l-4 border-red-500 p-4 mb-6 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
            <p class="text-red-400"><?php echo htmlspecialchars($error); ?></p>
        </div>
    </div>
<?php else: ?>
    <!-- Stream View -->
    <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="<?php echo $baseUrl; ?>/index.php" class="inline-flex items-center text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Cameras
            </a>
        </div>
        
        <!-- Camera Header -->
        <div class="glass rounded-2xl p-6 mb-6 animate-fade-in">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center space-x-4">
                    <div class="bg-red-600 px-4 py-2 rounded-full flex items-center space-x-2">
                        <span class="w-3 h-3 bg-white rounded-full pulse-badge"></span>
                        <span class="font-bold">LIVE</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($camera['name']); ?></h2>
                        <p class="text-gray-400 flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <?php echo htmlspecialchars($camera['location'] ?: 'No location specified'); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="flex space-x-2">
                    <button onclick="copyStreamUrl('<?php echo $camera['stream_key']; ?>')" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg transition"
                            title="Copy Stream URL">
                        <i class="fas fa-copy mr-2"></i>Copy URL
                    </button>
                    <button onclick="openInVLC('<?php echo htmlspecialchars($camera['rtsp_url']); ?>')" 
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg transition"
                            title="Open in VLC">
                        <i class="fas fa-external-link-alt mr-2"></i>VLC
                    </button>
                    <button onclick="toggleFullscreen()" 
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition"
                            title="Fullscreen">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Stream View -->
            <div class="lg:col-span-2">
                <div id="streamContainer" class="glass rounded-2xl overflow-hidden animate-fade-in">
                    <div class="bg-gray-900 aspect-video flex items-center justify-center relative">
                        <?php if ($mediamtxEnabled && $mediamtxStatus['connected']): ?>
                            <!-- HLS Video Player -->
                            <video id="videoPlayer" 
                                   class="w-full h-full" 
                                   controls 
                                   autoplay 
                                   muted
                                   data-stream-key="<?php echo htmlspecialchars($streamKey); ?>"
                                   data-hls-url="<?php echo htmlspecialchars($hlsUrl); ?>">
                                Your browser does not support the video tag.
                            </video>
                            
                            <!-- Loading Overlay -->
                            <div id="loadingOverlay" class="absolute inset-0 bg-gray-900 flex items-center justify-center">
                                <div class="text-center">
                                    <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500 mb-4"></div>
                                    <p class="text-lg">Loading stream...</p>
                                    <p class="text-sm text-gray-400 mt-2">Connecting to MediaMTX</p>
                                </div>
                            </div>
                            
                            <!-- Error Overlay -->
                            <div id="errorOverlay" class="absolute inset-0 bg-gray-900 hidden items-center justify-center">
                                <div class="text-center p-8">
                                    <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
                                    <h3 class="text-2xl font-bold mb-3">Stream Unavailable</h3>
                                    <p class="text-gray-400 mb-6 max-w-md mx-auto" id="errorMessage">
                                        Unable to load the video stream. The camera may be offline or MediaMTX is processing the stream.
                                    </p>
                                    <button onclick="retryStream()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                                        <i class="fas fa-redo mr-2"></i>Retry Connection
                                    </button>
                                    <button onclick="showRTSPFallback()" class="ml-2 px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                                        <i class="fas fa-info-circle mr-2"></i>Show RTSP Info
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Fallback: MediaMTX Not Available -->
                            <div class="text-center p-8">
                                <i class="fas fa-video text-8xl text-gray-700 mb-6"></i>
                                <h3 class="text-2xl font-bold mb-3">MediaMTX Unavailable</h3>
                                <p class="text-gray-400 mb-6 max-w-md mx-auto">
                                    <?php if (!$mediamtxEnabled): ?>
                                        MediaMTX streaming is not enabled. Configure MediaMTX to view streams in your browser.
                                    <?php else: ?>
                                        MediaMTX server is not reachable. Please check if MediaMTX is running.
                                    <?php endif; ?>
                                </p>
                                
                                <!-- Stream Options -->
                                <div class="space-y-3 max-w-md mx-auto">
                                    <div class="glass rounded-lg p-4 text-left">
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-play-circle text-blue-500 text-xl mt-1"></i>
                                            <div>
                                                <h4 class="font-bold mb-1">VLC Media Player</h4>
                                                <p class="text-sm text-gray-400">Open the RTSP URL directly in VLC</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="glass rounded-lg p-4 text-left">
                                        <div class="flex items-start space-x-3">
                                            <i class="fas fa-server text-purple-500 text-xl mt-1"></i>
                                            <div>
                                                <h4 class="font-bold mb-1">Setup MediaMTX</h4>
                                                <p class="text-sm text-gray-400">Install MediaMTX for browser playback</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Connection Status Overlay -->
                        <div id="statusOverlay" class="absolute bottom-4 right-4 bg-black/50 backdrop-blur-sm px-4 py-2 rounded-lg">
                            <div class="flex items-center space-x-2 text-sm">
                                <span id="statusDot" class="w-2 h-2 bg-yellow-500 rounded-full pulse-badge"></span>
                                <span id="statusText">Connecting...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Info Panel -->
            <div class="space-y-6">
                <!-- Camera Details -->
                <div class="glass rounded-2xl p-6 animate-fade-in">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Camera Details
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">STREAM KEY</p>
                            <code class="text-xs bg-black/30 px-3 py-2 rounded block break-all">
                                <?php echo htmlspecialchars($camera['stream_key']); ?>
                            </code>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-400 mb-1">RTSP URL</p>
                            <code class="text-xs bg-black/30 px-3 py-2 rounded block break-all">
                                <?php echo htmlspecialchars($camera['rtsp_url']); ?>
                            </code>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-400 mb-1">ADDED</p>
                            <p class="text-sm">
                                <?php echo date('M d, Y H:i', strtotime($camera['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Stream Stats -->
                <div class="glass rounded-2xl p-6 animate-fade-in">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-chart-line mr-2 text-green-500"></i>
                        Stream Stats
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Status</span>
                            <span class="px-3 py-1 bg-green-900/30 text-green-400 rounded-full text-xs font-medium">
                                Active
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Protocol</span>
                            <span class="font-mono text-sm"><?php echo $mediamtxEnabled && $mediamtxStatus['connected'] ? 'HLS' : 'RTSP'; ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">MediaMTX</span>
                            <?php if ($mediamtxEnabled && $mediamtxStatus['connected']): ?>
                                <span class="text-green-400 flex items-center">
                                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                    Online
                                </span>
                            <?php else: ?>
                                <span class="text-red-400 flex items-center">
                                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                    Offline
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Quality</span>
                            <span class="font-mono text-sm" id="qualityInfo">Auto</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Bitrate</span>
                            <span class="font-mono text-sm" id="bitrateInfo">--</span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Share Options -->
                <div class="glass rounded-2xl p-6 animate-fade-in">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-share-alt mr-2 text-purple-500"></i>
                        Share Stream
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-400 mb-2">Share this URL</p>
                            <div class="flex space-x-2">
                                <input type="text" readonly
                                       value="<?php echo $baseUrl; ?>/view.php?key=<?php echo htmlspecialchars($camera['stream_key']); ?>"
                                       class="flex-1 px-3 py-2 bg-black/30 rounded text-sm"
                                       id="shareUrl">
                                <button onclick="copyShareUrl()" 
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded transition">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- HLS.js Library -->
<script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

<!-- Video Player Initialization -->
<script>
// Video player initialization for view.php
(function() {
    const videoPlayer = document.getElementById('videoPlayer');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const errorOverlay = document.getElementById('errorOverlay');
    const errorMessage = document.getElementById('errorMessage');
    const statusDot = document.getElementById('statusDot');
    const statusText = document.getElementById('statusText');
    const statusOverlay = document.getElementById('statusOverlay');
    const qualityInfo = document.getElementById('qualityInfo');
    const bitrateInfo = document.getElementById('bitrateInfo');
    
    if (!videoPlayer) {
        return; // Not on video page
    }
    
    const hlsUrl = videoPlayer.dataset.hlsUrl;
    const streamKey = videoPlayer.dataset.streamKey;
    let hls = null;
    let retryCount = 0;
    const maxRetries = 3;
    
    function updateStatus(status, text) {
        if (!statusDot || !statusText) return;
        
        statusDot.className = 'w-2 h-2 rounded-full pulse-badge';
        
        switch(status) {
            case 'connecting':
                statusDot.classList.add('bg-yellow-500');
                statusText.textContent = text || 'Connecting...';
                break;
            case 'playing':
                statusDot.classList.add('bg-green-500');
                statusText.textContent = text || 'Live';
                break;
            case 'error':
                statusDot.classList.add('bg-red-500');
                statusText.textContent = text || 'Error';
                break;
            case 'buffering':
                statusDot.classList.add('bg-blue-500');
                statusText.textContent = text || 'Buffering...';
                break;
        }
    }
    
    function hideLoading() {
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
    }
    
    function showError(message) {
        hideLoading();
        if (errorOverlay && errorMessage) {
            errorMessage.textContent = message;
            errorOverlay.classList.remove('hidden');
            errorOverlay.classList.add('flex');
        }
        updateStatus('error', 'Offline');
    }
    
    function hideError() {
        if (errorOverlay) {
            errorOverlay.classList.add('hidden');
            errorOverlay.classList.remove('flex');
        }
    }
    
    function initPlayer() {
        if (Hls.isSupported()) {
            hls = new Hls({
                enableWorker: true,
                lowLatencyMode: true,
                backBufferLength: 90
            });
            
            hls.loadSource(hlsUrl);
            hls.attachMedia(videoPlayer);
            
            hls.on(Hls.Events.MANIFEST_PARSED, function() {
                console.log('HLS manifest loaded');
                hideLoading();
                hideError();
                videoPlayer.play().catch(e => {
                    console.log('Autoplay prevented:', e);
                    // Autoplay might be blocked, user needs to click play
                });
                updateStatus('playing', 'Live');
            });
            
            hls.on(Hls.Events.LEVEL_LOADED, function(event, data) {
                if (qualityInfo) {
                    qualityInfo.textContent = data.details.levelInfo || 'Auto';
                }
            });
            
            hls.on(Hls.Events.FRAG_LOADED, function(event, data) {
                if (bitrateInfo && data.frag) {
                    const bitrate = Math.round(data.frag.stats.loaded * 8 / data.frag.duration / 1000);
                    bitrateInfo.textContent = bitrate > 0 ? bitrate + ' kbps' : '--';
                }
            });
            
            hls.on(Hls.Events.ERROR, function(event, data) {
                console.error('HLS error:', data);
                
                if (data.fatal) {
                    switch(data.type) {
                        case Hls.ErrorTypes.NETWORK_ERROR:
                            console.error('Network error');
                            if (retryCount < maxRetries) {
                                retryCount++;
                                updateStatus('error', 'Retrying...');
                                setTimeout(() => {
                                    hls.startLoad();
                                }, 2000);
                            } else {
                                showError('Unable to load stream. The camera may be offline or not yet registered with MediaMTX.');
                            }
                            break;
                        case Hls.ErrorTypes.MEDIA_ERROR:
                            console.error('Media error, try to recover');
                            hls.recoverMediaError();
                            break;
                        default:
                            showError('Fatal error loading stream: ' + data.details);
                            hls.destroy();
                            break;
                    }
                }
            });
            
        } else if (videoPlayer.canPlayType('application/vnd.apple.mpegurl')) {
            // Native HLS support (Safari)
            videoPlayer.src = hlsUrl;
            videoPlayer.addEventListener('loadedmetadata', function() {
                hideLoading();
                hideError();
                updateStatus('playing', 'Live');
            });
            
            videoPlayer.addEventListener('error', function(e) {
                console.error('Video error:', e);
                showError('Unable to load stream. The camera may be offline.');
            });
        } else {
            showError('Your browser does not support HLS video playback.');
        }
    }
    
    // Video event listeners
    if (videoPlayer) {
        videoPlayer.addEventListener('playing', function() {
            updateStatus('playing', 'Live');
        });
        
        videoPlayer.addEventListener('waiting', function() {
            updateStatus('buffering', 'Buffering...');
        });
        
        videoPlayer.addEventListener('pause', function() {
            if (statusOverlay) {
                updateStatus('connecting', 'Paused');
            }
        });
    }
    
    // Retry button handler
    window.retryStream = function() {
        retryCount = 0;
        hideError();
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
        }
        updateStatus('connecting', 'Connecting...');
        
        if (hls) {
            hls.destroy();
        }
        
        setTimeout(initPlayer, 1000);
    };
    
    // Show RTSP fallback
    window.showRTSPFallback = function() {
        hideError();
        if (loadingOverlay) {
            loadingOverlay.innerHTML = `
                <div class="text-center p-8">
                    <i class="fas fa-info-circle text-6xl text-blue-500 mb-4"></i>
                    <h3 class="text-2xl font-bold mb-3">RTSP Stream Information</h3>
                    <p class="text-gray-400 mb-4">Use the RTSP URL below with VLC or another RTSP-compatible player</p>
                    <div class="glass rounded-lg p-4 mb-4">
                        <code class="text-sm break-all"><?php echo htmlspecialchars($camera['rtsp_url'] ?? ''); ?></code>
                    </div>
                    <button onclick="retryStream()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                        <i class="fas fa-redo mr-2"></i>Try HLS Again
                    </button>
                </div>
            `;
            loadingOverlay.style.display = 'flex';
        }
    };
    
    // Initialize player when page loads
    if (hlsUrl && streamKey) {
        updateStatus('connecting', 'Connecting...');
        initPlayer();
    }
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
