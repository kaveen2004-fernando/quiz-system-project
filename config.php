<?php
// config.php - Update these values with your actual database credentials
session_start();

// Database configuration
define('DB_HOST', 'localhost');        // Usually 'localhost'
define('DB_NAME', 'quiz_system');      // Your database name
define('DB_USER', 'root');             // Your MySQL username
define('DB_PASS', '');                 // Your MySQL password

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


// Site configuration
define('SITE_NAME', 'Quiz System');
define('SITE_URL', 'http://localhost/quiz-system');
define('UPLOAD_PATH', 'uploads/');

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>