<?php
/**
 * Logout Page
 * Destroys session and redirects to login page
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// Logout the user
logoutUser();

// Redirect to login page
$baseUrl = getBaseUrl();
header('Location: ' . $baseUrl . '/login.php');
exit;
