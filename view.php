<?php
/**
 * View Stream Page
 * Dedicated page for viewing a specific camera stream
 */

$pageTitle = 'View Stream - Live CCTV Manager';
require_once __DIR__ . '/includes/header.php';

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
                        <!-- Stream Placeholder -->
                        <div class="text-center p-8">
                            <i class="fas fa-video text-8xl text-gray-700 mb-6"></i>
                            <h3 class="text-2xl font-bold mb-3">RTSP Stream Active</h3>
                            <p class="text-gray-400 mb-6 max-w-md mx-auto">
                                RTSP streams cannot be played directly in web browsers. 
                                Use one of the methods below to view the stream.
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
                                            <h4 class="font-bold mb-1">MediaMTX / FFmpeg</h4>
                                            <p class="text-sm text-gray-400">Convert RTSP to HLS/WebRTC for browser playback</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Connection Status Overlay -->
                        <div class="absolute bottom-4 right-4 bg-black/50 backdrop-blur-sm px-4 py-2 rounded-lg">
                            <div class="flex items-center space-x-2 text-sm">
                                <span class="w-2 h-2 bg-green-500 rounded-full pulse-badge"></span>
                                <span>Connected</span>
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
                            <span class="font-mono text-sm">RTSP</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Connection</span>
                            <span class="text-green-400 flex items-center">
                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                Connected
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
