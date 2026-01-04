<?php
/**
 * Admin Panel - CRUD Operations for Cameras
 * Dashboard with statistics and camera management
 */

$pageTitle = 'Admin Panel - Live CCTV Manager';
require_once __DIR__ . '/includes/header.php';

// Fetch statistics
try {
    $pdo = getDbConnection();
    
    // Total cameras
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cameras");
    $totalCameras = $stmt->fetch()['total'];
    
    // Active cameras
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM cameras WHERE status = 'active'");
    $activeCameras = $stmt->fetch()['total'];
    
    // Inactive cameras
    $inactiveCameras = $totalCameras - $activeCameras;
    
    // Fetch all cameras
    $stmt = $pdo->query("SELECT * FROM cameras ORDER BY created_at DESC");
    $cameras = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $cameras = [];
    $error = "Error: " . $e->getMessage();
    $totalCameras = $activeCameras = $inactiveCameras = 0;
}
?>

<!-- Page Header -->
<div class="mb-8 animate-fade-in">
    <h2 class="text-3xl font-bold mb-2">Admin Dashboard</h2>
    <p class="text-gray-400">Manage your CCTV cameras and streams</p>
</div>

<?php if (isset($error)): ?>
    <div class="glass border-l-4 border-red-500 p-4 mb-6 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
            <p class="text-red-400"><?php echo htmlspecialchars($error); ?></p>
        </div>
    </div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Cameras -->
    <div class="glass rounded-2xl p-6 animate-fade-in">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm mb-1">Total Cameras</p>
                <p class="text-3xl font-bold"><?php echo $totalCameras; ?></p>
            </div>
            <div class="bg-blue-600 w-12 h-12 rounded-lg flex items-center justify-center">
                <i class="fas fa-video text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Active Cameras -->
    <div class="glass rounded-2xl p-6 animate-fade-in">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm mb-1">Active Cameras</p>
                <p class="text-3xl font-bold text-green-500"><?php echo $activeCameras; ?></p>
            </div>
            <div class="bg-green-600 w-12 h-12 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Inactive Cameras -->
    <div class="glass rounded-2xl p-6 animate-fade-in">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm mb-1">Inactive Cameras</p>
                <p class="text-3xl font-bold text-red-500"><?php echo $inactiveCameras; ?></p>
            </div>
            <div class="bg-red-600 w-12 h-12 rounded-lg flex items-center justify-center">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Add Camera Button -->
<div class="mb-6 flex justify-between items-center">
    <h3 class="text-2xl font-bold">Camera Management</h3>
    <button onclick="openAddModal()" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition">
        <i class="fas fa-plus mr-2"></i>Add New Camera
    </button>
</div>

<!-- Cameras Table -->
<div class="glass rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-white/5">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">RTSP URL</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Stream Key</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                <?php if (empty($cameras)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No cameras found. Add your first camera to get started.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cameras as $camera): ?>
                        <tr class="hover:bg-white/5 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium"><?php echo htmlspecialchars($camera['name']); ?></div>
                            </td>
                            <td class="px-6 py-4 text-gray-400">
                                <?php echo htmlspecialchars($camera['location'] ?: '-'); ?>
                            </td>
                            <td class="px-6 py-4">
                                <code class="text-xs text-blue-400 bg-blue-900/30 px-2 py-1 rounded">
                                    <?php echo htmlspecialchars(substr($camera['rtsp_url'], 0, 30)) . '...'; ?>
                                </code>
                            </td>
                            <td class="px-6 py-4">
                                <code class="text-xs text-purple-400 bg-purple-900/30 px-2 py-1 rounded">
                                    <?php echo htmlspecialchars(substr($camera['stream_key'], 0, 16)) . '...'; ?>
                                </code>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($camera['status'] === 'active'): ?>
                                    <span class="px-3 py-1 bg-green-900/30 text-green-400 rounded-full text-xs font-medium">
                                        <i class="fas fa-circle text-xs mr-1"></i>Active
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-red-900/30 text-red-400 rounded-full text-xs font-medium">
                                        <i class="fas fa-circle text-xs mr-1"></i>Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick='editCamera(<?php echo json_encode($camera); ?>)' 
                                            class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 rounded transition text-sm"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleStatus(<?php echo $camera['id']; ?>, '<?php echo $camera['status']; ?>')" 
                                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 rounded transition text-sm"
                                            title="Toggle Status">
                                        <i class="fas fa-toggle-on"></i>
                                    </button>
                                    <button onclick="deleteCamera(<?php echo $camera['id']; ?>, '<?php echo htmlspecialchars(addslashes($camera['name'])); ?>')" 
                                            class="px-3 py-1 bg-red-600 hover:bg-red-700 rounded transition text-sm"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="cameraModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
    <div class="glass rounded-2xl p-8 max-w-2xl w-full mx-4 animate-fade-in">
        <div class="flex justify-between items-center mb-6">
            <h3 id="modalTitle" class="text-2xl font-bold">Add New Camera</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="cameraForm" class="space-y-4">
            <input type="hidden" id="cameraId" name="id">
            
            <div>
                <label class="block text-sm font-medium mb-2">Camera Name</label>
                <input type="text" id="cameraName" name="name" required
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-blue-500 transition"
                       placeholder="e.g., Main Entrance Camera">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Location</label>
                <input type="text" id="cameraLocation" name="location"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-blue-500 transition"
                       placeholder="e.g., Building A - Floor 1">
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">RTSP URL</label>
                <input type="text" id="cameraRtsp" name="rtsp_url" required
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-blue-500 transition font-mono text-sm"
                       placeholder="rtsp://username:password@ip:port/stream">
                <p class="text-xs text-gray-400 mt-1">
                    <i class="fas fa-info-circle mr-1"></i>
                    Example: rtsp://admin:password@192.168.1.100:554/stream1
                </p>
            </div>
            
            <div>
                <label class="block text-sm font-medium mb-2">Status</label>
                <select id="cameraStatus" name="status"
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-blue-500 transition">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            
            <div class="flex space-x-3 pt-4">
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg transition font-medium">
                    <i class="fas fa-save mr-2"></i>Save Camera
                </button>
                <button type="button" onclick="closeModal()" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
