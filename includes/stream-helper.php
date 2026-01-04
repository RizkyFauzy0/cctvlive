<?php
/**
 * Stream Helper Functions
 * Helper functions for MediaMTX stream management
 */

require_once __DIR__ . '/../config/mediamtx.php';

/**
 * Check if MediaMTX server is available
 * 
 * @return bool True if MediaMTX is reachable
 */
function checkMediaMTXConnection() {
    if (!isMediaMTXEnabled()) {
        return false;
    }
    
    $apiUrl = getMediaMTXApiUrl() . '/v3/config/global/get';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, MEDIAMTX_TIMEOUT);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, MEDIAMTX_TIMEOUT);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}

/**
 * Register RTSP stream to MediaMTX
 * 
 * @param string $streamKey Unique stream identifier
 * @param string $rtspUrl RTSP stream URL
 * @return array Result with success status and message
 */
function registerStreamToMediaMTX($streamKey, $rtspUrl) {
    if (!isMediaMTXEnabled()) {
        return [
            'success' => false,
            'message' => 'MediaMTX is not enabled'
        ];
    }
    
    if (!MEDIAMTX_AUTO_REGISTER) {
        return [
            'success' => false,
            'message' => 'Auto-registration is disabled'
        ];
    }
    
    // Check connection first
    if (!checkMediaMTXConnection()) {
        return [
            'success' => false,
            'message' => 'MediaMTX server is not reachable'
        ];
    }
    
    $apiUrl = getMediaMTXApiUrl() . '/v3/config/paths/add/' . urlencode($streamKey);
    
    $data = [
        'source' => $rtspUrl,
        'sourceProtocol' => 'automatic',
        'sourceOnDemand' => true,
        'runOnDemand' => '',
        'runOnDemandRestart' => false,
        'runOnDemandStartTimeout' => '10s',
        'runOnDemandCloseAfter' => '10s'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, MEDIAMTX_TIMEOUT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return [
            'success' => true,
            'message' => 'Stream registered successfully',
            'stream_key' => $streamKey,
            'hls_url' => getMediaMTXHlsManifestUrl($streamKey)
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to register stream: ' . ($error ?: 'HTTP ' . $httpCode),
            'http_code' => $httpCode
        ];
    }
}

/**
 * Unregister stream from MediaMTX
 * 
 * @param string $streamKey Unique stream identifier
 * @return array Result with success status and message
 */
function unregisterStreamFromMediaMTX($streamKey) {
    if (!isMediaMTXEnabled()) {
        return [
            'success' => false,
            'message' => 'MediaMTX is not enabled'
        ];
    }
    
    if (!MEDIAMTX_AUTO_UNREGISTER) {
        return [
            'success' => false,
            'message' => 'Auto-unregistration is disabled'
        ];
    }
    
    $apiUrl = getMediaMTXApiUrl() . '/v3/config/paths/remove/' . urlencode($streamKey);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, MEDIAMTX_TIMEOUT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // 200 = success, 404 = path doesn't exist (also acceptable)
    if ($httpCode >= 200 && $httpCode < 300 || $httpCode == 404) {
        return [
            'success' => true,
            'message' => 'Stream unregistered successfully'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to unregister stream',
            'http_code' => $httpCode
        ];
    }
}

/**
 * Get stream status from MediaMTX
 * 
 * @param string $streamKey Unique stream identifier
 * @return array Stream status information
 */
function getStreamStatus($streamKey) {
    if (!isMediaMTXEnabled()) {
        return [
            'success' => false,
            'message' => 'MediaMTX is not enabled',
            'status' => 'unavailable'
        ];
    }
    
    $apiUrl = getMediaMTXApiUrl() . '/v3/paths/get/' . urlencode($streamKey);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, MEDIAMTX_TIMEOUT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        return [
            'success' => true,
            'status' => 'active',
            'data' => $data
        ];
    } elseif ($httpCode == 404) {
        return [
            'success' => false,
            'status' => 'not_registered',
            'message' => 'Stream is not registered in MediaMTX'
        ];
    } else {
        return [
            'success' => false,
            'status' => 'error',
            'message' => 'Failed to get stream status',
            'http_code' => $httpCode
        ];
    }
}

/**
 * Update stream configuration in MediaMTX
 * 
 * @param string $streamKey Unique stream identifier
 * @param string $rtspUrl New RTSP stream URL
 * @return array Result with success status and message
 */
function updateStreamInMediaMTX($streamKey, $rtspUrl) {
    // First unregister the old stream
    $unregisterResult = unregisterStreamFromMediaMTX($streamKey);
    
    // Then register with new URL
    // Add a small delay to ensure cleanup
    usleep(100000); // 100ms
    
    return registerStreamToMediaMTX($streamKey, $rtspUrl);
}

/**
 * Generate HLS URL for a stream
 * 
 * @param string $streamKey Unique stream identifier
 * @return string HLS manifest URL
 */
function generateHlsUrl($streamKey) {
    return getMediaMTXHlsManifestUrl($streamKey);
}

/**
 * Get MediaMTX server status
 * 
 * @return array Server status information
 */
function getMediaMTXStatus() {
    $isConnected = checkMediaMTXConnection();
    
    return [
        'enabled' => isMediaMTXEnabled(),
        'connected' => $isConnected,
        'api_url' => getMediaMTXApiUrl(),
        'hls_url' => MEDIAMTX_HLS_URL,
        'status' => $isConnected ? 'online' : 'offline'
    ];
}
