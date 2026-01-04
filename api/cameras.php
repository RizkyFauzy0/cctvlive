<?php
/**
 * REST API for Camera Management
 * Endpoints for CRUD operations
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight requests
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $pdo = getDbConnection();
    
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
            
        case 'POST':
            handlePost($pdo);
            break;
            
        case 'PUT':
            handlePut($pdo);
            break;
            
        case 'DELETE':
            handleDelete($pdo);
            break;
            
        default:
            sendResponse(405, false, 'Method not allowed');
    }
} catch (Exception $e) {
    sendResponse(500, false, 'Server error: ' . $e->getMessage());
}

/**
 * Handle GET requests
 */
function handleGet($pdo) {
    // Get single camera by stream_key
    if (isset($_GET['stream_key'])) {
        $stmt = $pdo->prepare("SELECT * FROM cameras WHERE stream_key = ?");
        $stmt->execute([$_GET['stream_key']]);
        $camera = $stmt->fetch();
        
        if ($camera) {
            sendResponse(200, true, 'Camera found', $camera);
        } else {
            sendResponse(404, false, 'Camera not found');
        }
        return;
    }
    
    // Get single camera by ID
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM cameras WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $camera = $stmt->fetch();
        
        if ($camera) {
            sendResponse(200, true, 'Camera found', $camera);
        } else {
            sendResponse(404, false, 'Camera not found');
        }
        return;
    }
    
    // Get all cameras
    $status = $_GET['status'] ?? null;
    
    if ($status && in_array($status, ['active', 'inactive'])) {
        $stmt = $pdo->prepare("SELECT * FROM cameras WHERE status = ? ORDER BY created_at DESC");
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query("SELECT * FROM cameras ORDER BY created_at DESC");
    }
    
    $cameras = $stmt->fetchAll();
    sendResponse(200, true, 'Cameras retrieved', ['cameras' => $cameras, 'count' => count($cameras)]);
}

/**
 * Handle POST requests (Create)
 */
function handlePost($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($data['name']) || empty($data['rtsp_url'])) {
        sendResponse(400, false, 'Name and RTSP URL are required');
        return;
    }
    
    // Generate unique stream key
    $streamKey = generateStreamKey();
    
    // Check if stream key already exists (very unlikely)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cameras WHERE stream_key = ?");
    $stmt->execute([$streamKey]);
    
    while ($stmt->fetchColumn() > 0) {
        $streamKey = generateStreamKey();
        $stmt->execute([$streamKey]);
    }
    
    // Insert camera
    $stmt = $pdo->prepare("
        INSERT INTO cameras (name, location, rtsp_url, stream_key, status) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $data['name'],
        $data['location'] ?? null,
        $data['rtsp_url'],
        $streamKey,
        $data['status'] ?? 'active'
    ]);
    
    if ($success) {
        $cameraId = $pdo->lastInsertId();
        
        // Fetch the created camera
        $stmt = $pdo->prepare("SELECT * FROM cameras WHERE id = ?");
        $stmt->execute([$cameraId]);
        $camera = $stmt->fetch();
        
        sendResponse(201, true, 'Camera created successfully', $camera);
    } else {
        sendResponse(500, false, 'Failed to create camera');
    }
}

/**
 * Handle PUT requests (Update)
 */
function handlePut($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate ID
    if (empty($data['id'])) {
        sendResponse(400, false, 'Camera ID is required');
        return;
    }
    
    // Check if camera exists
    $stmt = $pdo->prepare("SELECT * FROM cameras WHERE id = ?");
    $stmt->execute([$data['id']]);
    $camera = $stmt->fetch();
    
    if (!$camera) {
        sendResponse(404, false, 'Camera not found');
        return;
    }
    
    // Build update query dynamically
    $updateFields = [];
    $params = [];
    
    if (isset($data['name'])) {
        $updateFields[] = "name = ?";
        $params[] = $data['name'];
    }
    
    if (isset($data['location'])) {
        $updateFields[] = "location = ?";
        $params[] = $data['location'];
    }
    
    if (isset($data['rtsp_url'])) {
        $updateFields[] = "rtsp_url = ?";
        $params[] = $data['rtsp_url'];
    }
    
    if (isset($data['status']) && in_array($data['status'], ['active', 'inactive'])) {
        $updateFields[] = "status = ?";
        $params[] = $data['status'];
    }
    
    if (empty($updateFields)) {
        sendResponse(400, false, 'No valid fields to update');
        return;
    }
    
    $params[] = $data['id'];
    $sql = "UPDATE cameras SET " . implode(', ', $updateFields) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($sql);
    $success = $stmt->execute($params);
    
    if ($success) {
        // Fetch updated camera
        $stmt = $pdo->prepare("SELECT * FROM cameras WHERE id = ?");
        $stmt->execute([$data['id']]);
        $camera = $stmt->fetch();
        
        sendResponse(200, true, 'Camera updated successfully', $camera);
    } else {
        sendResponse(500, false, 'Failed to update camera');
    }
}

/**
 * Handle DELETE requests
 */
function handleDelete($pdo) {
    // Get ID from query string or JSON body
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
    }
    
    if (!$id) {
        sendResponse(400, false, 'Camera ID is required');
        return;
    }
    
    // Check if camera exists
    $stmt = $pdo->prepare("SELECT * FROM cameras WHERE id = ?");
    $stmt->execute([$id]);
    $camera = $stmt->fetch();
    
    if (!$camera) {
        sendResponse(404, false, 'Camera not found');
        return;
    }
    
    // Delete camera
    $stmt = $pdo->prepare("DELETE FROM cameras WHERE id = ?");
    $success = $stmt->execute([$id]);
    
    if ($success) {
        sendResponse(200, true, 'Camera deleted successfully', ['id' => $id]);
    } else {
        sendResponse(500, false, 'Failed to delete camera');
    }
}

/**
 * Send JSON response
 */
function sendResponse($code, $success, $message, $data = null) {
    http_response_code($code);
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}
