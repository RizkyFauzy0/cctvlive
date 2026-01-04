<?php
/**
 * Login Page - Admin Authentication
 * Modern UI with glass morphism and dark theme
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

// If already logged in, redirect to admin page
if (isLoggedIn()) {
    $baseUrl = getBaseUrl();
    header('Location: ' . $baseUrl . '/admin.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Authenticate user
        $result = authenticateUser($username, $password);
        
        if ($result['success']) {
            // Login successful
            loginUser($result['user'], $remember);
            
            // Redirect to requested page or admin page
            $baseUrl = getBaseUrl();
            $redirect = $_GET['redirect'] ?? '/admin.php';
            header('Location: ' . $baseUrl . $redirect);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Admin Login - Live CCTV Manager';
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon input {
            padding-left: 2.75rem;
        }
        
        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
    </style>
</head>
<body class="text-gray-100">
    <!-- Background Decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-0 w-96 h-96 bg-blue-600/20 rounded-full filter blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-600/20 rounded-full filter blur-3xl"></div>
    </div>

    <!-- Main Container -->
    <div class="relative min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            <!-- Logo/Header -->
            <div class="text-center mb-8 animate-fade-in">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-600 rounded-2xl mb-4">
                    <i class="fas fa-video text-4xl text-white"></i>
                </div>
                <h1 class="text-4xl font-bold gradient-text mb-2">Live CCTV Manager</h1>
                <p class="text-gray-400">Admin Panel Login</p>
            </div>

            <!-- Login Card -->
            <div class="glass rounded-2xl p-8 shadow-2xl animate-fade-in">
                <h2 class="text-2xl font-bold mb-6 text-center">Welcome Back</h2>
                
                <?php if ($error): ?>
                    <div class="mb-6 p-4 bg-red-900/30 border border-red-500 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                            <p class="text-red-400 text-sm"><?php echo htmlspecialchars($error); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="mb-6 p-4 bg-green-900/30 border border-green-500 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-check-circle text-green-400"></i>
                            <p class="text-green-400 text-sm"><?php echo htmlspecialchars($success); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="" class="space-y-6">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-medium mb-2">Username</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required
                                autocomplete="username"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-blue-500 transition"
                                placeholder="Enter your username"
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium mb-2">Password</label>
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required
                                autocomplete="current-password"
                                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-lg focus:outline-none focus:border-blue-500 transition"
                                placeholder="Enter your password"
                            >
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                                class="w-4 h-4 rounded bg-white/5 border-white/10 text-blue-600 focus:ring-blue-500 focus:ring-2"
                            >
                            <span class="text-sm text-gray-400">Remember me</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-lg font-medium transition transform hover:scale-[1.02] active:scale-[0.98]"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </form>

                <!-- Info Section -->
                <div class="mt-6 pt-6 border-t border-white/10 text-center text-sm text-gray-400">
                    <p>
                        <i class="fas fa-info-circle mr-1"></i>
                        Default credentials: <code class="text-blue-400">admin / admin123</code>
                    </p>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="mt-6 text-center">
                <a href="<?php echo $baseUrl; ?>/index.php" class="inline-flex items-center space-x-2 text-gray-400 hover:text-white transition">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Home</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
