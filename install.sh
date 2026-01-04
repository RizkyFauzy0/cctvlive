#!/bin/bash

# Live CCTV Manager - Installation Script
# This script helps automate the installation process

echo "=========================================="
echo "Live CCTV Manager - Installation Script"
echo "=========================================="
echo ""

# Check if MySQL is installed
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL is not installed. Please install MySQL first."
    exit 1
fi

echo "✓ MySQL is installed"

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP first."
    exit 1
fi

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
echo "✓ PHP version $PHP_VERSION is installed"

# Prompt for database credentials
echo ""
echo "Please enter your MySQL database credentials:"
read -p "MySQL Host [localhost]: " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "MySQL Username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "MySQL Password: " DB_PASS
echo ""

read -p "Database Name [cctvlive]: " DB_NAME
DB_NAME=${DB_NAME:-cctvlive}

# Test database connection
echo ""
echo "Testing database connection..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" &> /dev/null

if [ $? -ne 0 ]; then
    echo "❌ Failed to connect to MySQL. Please check your credentials."
    exit 1
fi

echo "✓ Database connection successful"

# Update config file
echo ""
echo "Updating configuration file..."

cat > config/database.php << EOF
<?php
/**
 * Database Configuration
 * Modify these settings according to your environment
 */

// Database connection settings
define('DB_HOST', '$DB_HOST');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');
define('DB_CHARSET', 'utf8mb4');

// Create PDO connection
function getDbConnection() {
    static \$pdo = null;
    
    if (\$pdo === null) {
        try {
            \$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            \$options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);
        } catch (PDOException \$e) {
            die("Database connection failed: " . \$e->getMessage());
        }
    }
    
    return \$pdo;
}

// Generate unique stream key
function generateStreamKey() {
    return bin2hex(random_bytes(16));
}

// Get base URL
function getBaseUrl() {
    \$protocol = isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS'] === 'on' ? "https" : "http";
    \$host = \$_SERVER['HTTP_HOST'];
    \$script = dirname(\$_SERVER['SCRIPT_NAME']);
    
    // Remove trailing slash if present to avoid double slashes
    \$script = rtrim(\$script, '/');
    
    return \$protocol . "://" . \$host . \$script;
}
EOF

echo "✓ Configuration updated"

# Import database schema
echo ""
echo "Creating database and importing schema..."

mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" < database.sql

if [ $? -ne 0 ]; then
    echo "❌ Failed to import database schema."
    exit 1
fi

echo "✓ Database schema imported successfully"

# Set permissions
echo ""
echo "Setting file permissions..."
chmod 644 config/database.php
chmod 755 assets/js/
echo "✓ Permissions set"

echo ""
echo "=========================================="
echo "✅ Installation completed successfully!"
echo "=========================================="
echo ""
echo "Default admin credentials:"
echo "  Username: admin"
echo "  Password: admin123"
echo ""
echo "⚠️  IMPORTANT: Change the default password after first login!"
echo ""
echo "Next steps:"
echo "1. Configure your web server to point to this directory"
echo "2. Access the application via your web browser"
echo "3. Log in with the default credentials"
echo "4. Start adding your CCTV cameras!"
echo ""
