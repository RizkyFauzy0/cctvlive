<?php
/**
 * Main Index Page - Camera Grid View
 * Displays all active cameras with preview cards
 */

$pageTitle = 'Live CCTV Manager - Home';
require_once __DIR__ . '/includes/header.php';

// Fetch all active cameras
try {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT * FROM cameras WHERE status = 'active' ORDER BY created_at DESC");
    $stmt->execute();
    $cameras = $stmt->fetchAll();
} catch (PDOException $e) {
    $cameras = [];
    $error = "Error fetching cameras: " . $e->getMessage();
}
?>

<!-- Hero Section -->
<div class="text-center mb-12 animate-fade-in">
    <h2 class="text-4xl font-bold mb-4 gradient-text">Live Camera Feeds</h2>
    <p class="text-gray-400 text-lg">Monitor your locations in real-time</p>
</div>

<?php if (isset($error)): ?>
    <div class="glass border-l-4 border-red-500 p-4 mb-6 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
            <p class="text-red-400"><?php echo htmlspecialchars($error); ?></p>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($cameras)): ?>
    <!-- Empty State -->
    <div class="glass rounded-2xl p-12 text-center animate-fade-in">
        <i class="fas fa-video-slash text-6xl text-gray-600 mb-4"></i>
        <h3 class="text-2xl font-bold mb-2">No Active Cameras</h3>
        <p class="text-gray-400 mb-6">Add cameras from the admin panel to start monitoring</p>
        <a href="<?php echo $baseUrl; ?>/admin.php" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition">
            <i class="fas fa-plus mr-2"></i>Add Camera
        </a>
    </div>
<?php else: ?>
    <!-- Camera Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($cameras as $camera): ?>
            <div class="glass glass-hover rounded-2xl overflow-hidden animate-fade-in">
                <!-- Camera Preview -->
                <div class="relative bg-gray-900 h-48 flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-video text-6xl text-gray-700 mb-3"></i>
                        <p class="text-gray-500 text-sm">RTSP Stream</p>
                    </div>
                    
                    <!-- Live Badge -->
                    <div class="absolute top-3 right-3 bg-red-600 px-3 py-1 rounded-full flex items-center space-x-2">
                        <span class="w-2 h-2 bg-white rounded-full pulse-badge"></span>
                        <span class="text-xs font-bold">LIVE</span>
                    </div>
                </div>
                
                <!-- Camera Info -->
                <div class="p-5">
                    <h3 class="text-xl font-bold mb-2 truncate"><?php echo htmlspecialchars($camera['name']); ?></h3>
                    <p class="text-gray-400 text-sm mb-4 flex items-center">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        <?php echo htmlspecialchars($camera['location'] ?: 'No location'); ?>
                    </p>
                    
                    <!-- Actions -->
                    <div class="flex space-x-2">
                        <a href="<?php echo $baseUrl; ?>/view.php?key=<?php echo urlencode($camera['stream_key']); ?>" 
                           class="flex-1 bg-blue-600 hover:bg-blue-700 text-center py-2 rounded-lg transition">
                            <i class="fas fa-play mr-2"></i>Watch
                        </a>
                        <button onclick="copyStreamUrl('<?php echo $camera['stream_key']; ?>')" 
                                class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition" 
                                title="Copy Stream URL">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Stats Footer -->
    <div class="mt-12 text-center text-gray-400">
        <p><i class="fas fa-camera mr-2"></i><?php echo count($cameras); ?> active camera(s) streaming</p>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
