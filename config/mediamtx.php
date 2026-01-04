<?php
/**
 * MediaMTX Configuration
 * Configuration for MediaMTX streaming server integration
 */

// MediaMTX API Configuration
define('MEDIAMTX_API_URL', getenv('MEDIAMTX_API_URL') ?: 'http://localhost:9997');
define('MEDIAMTX_API_ENABLED', true);

// MediaMTX HLS Configuration
define('MEDIAMTX_HLS_URL', getenv('MEDIAMTX_HLS_URL') ?: 'http://localhost:8888');
define('MEDIAMTX_HLS_ENABLED', true);

// MediaMTX WebRTC Configuration
define('MEDIAMTX_WEBRTC_URL', getenv('MEDIAMTX_WEBRTC_URL') ?: 'http://localhost:8889');
define('MEDIAMTX_WEBRTC_ENABLED', false); // WebRTC disabled by default

// MediaMTX RTSP Configuration
define('MEDIAMTX_RTSP_URL', getenv('MEDIAMTX_RTSP_URL') ?: 'rtsp://localhost:8554');

// MediaMTX Connection Settings
define('MEDIAMTX_TIMEOUT', 5); // Connection timeout in seconds
define('MEDIAMTX_RETRY_ATTEMPTS', 3); // Number of retry attempts

// MediaMTX Stream Settings
define('MEDIAMTX_AUTO_REGISTER', true); // Auto-register streams on camera creation
define('MEDIAMTX_AUTO_UNREGISTER', true); // Auto-unregister streams on camera deletion

/**
 * Get MediaMTX API URL
 */
function getMediaMTXApiUrl() {
    return MEDIAMTX_API_URL;
}

/**
 * Get MediaMTX HLS URL for a stream
 */
function getMediaMTXHlsUrl($streamKey) {
    return MEDIAMTX_HLS_URL . '/' . $streamKey . '/';
}

/**
 * Get MediaMTX HLS manifest URL for a stream
 */
function getMediaMTXHlsManifestUrl($streamKey) {
    return getMediaMTXHlsUrl($streamKey) . 'index.m3u8';
}

/**
 * Get MediaMTX WebRTC URL for a stream
 */
function getMediaMTXWebRtcUrl($streamKey) {
    return MEDIAMTX_WEBRTC_URL . '/' . $streamKey;
}

/**
 * Check if MediaMTX is enabled
 */
function isMediaMTXEnabled() {
    return MEDIAMTX_API_ENABLED && MEDIAMTX_HLS_ENABLED;
}
