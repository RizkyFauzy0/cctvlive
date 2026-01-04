<?php
/**
 * MediaMTX Management API
 * Endpoints for managing MediaMTX streams
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/stream-helper.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle preflight requests
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
            
        case 'POST':
            handlePost();
            break;
            
        case 'DELETE':
            handleDelete();
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
function handleGet() {
    // Get MediaMTX status
    if (isset($_GET['action']) && $_GET['action'] === 'status') {
        $status = getMediaMTXStatus();
        sendResponse(200, true, 'MediaMTX status retrieved', $status);
        return;
    }
    
    // Get stream status
    if (isset($_GET['action']) && $_GET['action'] === 'stream_status' && isset($_GET['stream_key'])) {
        $streamKey = $_GET['stream_key'];
        $status = getStreamStatus($streamKey);
        
        // Add URLs to response
        $status['hls_url'] = generateHlsUrl($streamKey);
        if (MEDIAMTX_WEBRTC_ENABLED) {
            $status['webrtc_url'] = getMediaMTXWebRtcUrl($streamKey);
        }
        
        sendResponse(200, $status['success'], $status['message'] ?? 'Stream status retrieved', $status);
        return;
    }
    
    // Check MediaMTX connection
    if (isset($_GET['action']) && $_GET['action'] === 'check') {
        $isConnected = checkMediaMTXConnection();
        sendResponse(200, $isConnected, $isConnected ? 'MediaMTX is online' : 'MediaMTX is offline', [
            'connected' => $isConnected,
            'api_url' => getMediaMTXApiUrl()
        ]);
        return;
    }
    
    sendResponse(400, false, 'Invalid action or missing parameters');
}

/**
 * Handle POST requests
 */
function handlePost() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Register stream
    if (isset($data['action']) && $data['action'] === 'register') {
        if (empty($data['stream_key']) || empty($data['rtsp_url'])) {
            sendResponse(400, false, 'stream_key and rtsp_url are required');
            return;
        }
        
        $result = registerStreamToMediaMTX($data['stream_key'], $data['rtsp_url']);
        
        if ($result['success']) {
            sendResponse(200, true, $result['message'], [
                'stream_key' => $data['stream_key'],
                'hls_url' => $result['hls_url'],
                'hls_manifest' => getMediaMTXHlsManifestUrl($data['stream_key'])
            ]);
        } else {
            sendResponse(500, false, $result['message'], $result);
        }
        return;
    }
    
    // Update stream
    if (isset($data['action']) && $data['action'] === 'update') {
        if (empty($data['stream_key']) || empty($data['rtsp_url'])) {
            sendResponse(400, false, 'stream_key and rtsp_url are required');
            return;
        }
        
        $result = updateStreamInMediaMTX($data['stream_key'], $data['rtsp_url']);
        
        if ($result['success']) {
            sendResponse(200, true, $result['message'], [
                'stream_key' => $data['stream_key'],
                'hls_url' => $result['hls_url']
            ]);
        } else {
            sendResponse(500, false, $result['message'], $result);
        }
        return;
    }
    
    sendResponse(400, false, 'Invalid action or missing parameters');
}

/**
 * Handle DELETE requests
 */
function handleDelete() {
    // Get stream_key from query string or JSON body
    $streamKey = $_GET['stream_key'] ?? null;
    
    if (!$streamKey) {
        $data = json_decode(file_get_contents('php://input'), true);
        $streamKey = $data['stream_key'] ?? null;
    }
    
    if (!$streamKey) {
        sendResponse(400, false, 'stream_key is required');
        return;
    }
    
    $result = unregisterStreamFromMediaMTX($streamKey);
    
    if ($result['success']) {
        sendResponse(200, true, $result['message'], ['stream_key' => $streamKey]);
    } else {
        sendResponse(500, false, $result['message'], $result);
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
        if (is_array($data)) {
            $response = array_merge($response, $data);
        } else {
            $response['data'] = $data;
        }
    }
    
    echo json_encode($response);
    exit;
}
