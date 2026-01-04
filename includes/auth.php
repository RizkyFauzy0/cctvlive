<?php
/**
 * Authentication Helper Functions
 * Handles user authentication and session management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Require login - redirect to login page if not logged in
 * @param string $redirectUrl URL to redirect after login
 */
function requireLogin($redirectUrl = null) {
    if (!isLoggedIn()) {
        $baseUrl = getBaseUrl();
        $redirect = $redirectUrl ? '?redirect=' . urlencode($redirectUrl) : '';
        header('Location: ' . $baseUrl . '/login.php' . $redirect);
        exit;
    }
}

/**
 * Authenticate user with username and password
 * @param string $username
 * @param string $password
 * @return array ['success' => bool, 'message' => string, 'user' => array|null]
 */
function authenticateUser($username, $password) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid username or password',
                'user' => null
            ];
        }
    } catch (PDOException $e) {
        // Log error for debugging (in production, use proper logging)
        error_log('Authentication error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Authentication service temporarily unavailable',
            'user' => null
        ];
    }
}

/**
 * Login user - set session variables
 * @param array $user User data from database
 * @param bool $remember Whether to remember the user (optional)
 */
function loginUser($user, $remember = false) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'] ?? 'viewer';
    $_SESSION['login_time'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    // TODO: Implement remember me functionality if needed
    if ($remember) {
        // Set cookie for 30 days (optional implementation)
        // This is a placeholder for future implementation
    }
}

/**
 * Logout user - destroy session
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Get current logged in user info
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'] ?? 'viewer'
    ];
}

/**
 * Check if user has admin role
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

/**
 * Require admin role - redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $baseUrl = getBaseUrl();
        header('Location: ' . $baseUrl . '/index.php');
        exit;
    }
}
