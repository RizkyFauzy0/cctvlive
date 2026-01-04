<?php
require_once __DIR__ . '/../config/database.php';
$pageTitle = $pageTitle ?? 'Live CCTV Manager';
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="id">
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
        
        .glass-hover:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .pulse-badge {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
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
    </style>
</head>
<body class="text-gray-100">
    <!-- Navigation -->
    <nav class="glass border-b border-white/10 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-video text-blue-500 text-2xl"></i>
                    <h1 class="text-2xl font-bold gradient-text">Live CCTV Manager</h1>
                </div>
                <div class="flex space-x-4">
                    <a href="<?php echo $baseUrl; ?>/index.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    <a href="<?php echo $baseUrl; ?>/admin.php" class="px-4 py-2 rounded-lg hover:bg-white/10 transition">
                        <i class="fas fa-cog mr-2"></i>Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
