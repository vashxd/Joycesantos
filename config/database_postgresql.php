<?php
// PostgreSQL Database configuration
$host = 'localhost';
$port = '5432';
$dbname = 'joyce_santos_website';
$username = 'postgres';
$password = '12345678';

// PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    
    // For API endpoints, return JSON error
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }
    
    // For regular pages, show error message
    die("Database connection failed. Please try again later.");
}

// Database initialization function
function initializeDatabase() {
    global $pdo;
    
    try {
        // Check if user exists, if not create default user
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute(['santosjoyce56@gmail.com']);
        
        if ($stmt->fetchColumn() == 0) {
            // Create default user
            $password_hash = password_hash('159753', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, role) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute(['joyce', 'santosjoyce56@gmail.com', $password_hash, 'admin']);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Database initialization error: " . $e->getMessage());
        return false;
    }
}

// Helper functions
function executeQuery($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query error: " . $e->getMessage());
        throw $e;
    }
}

function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

function getLastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}

// Blog functions
function getBlogPosts($limit = null, $offset = 0, $status = 'published') {
    $sql = "SELECT bp.*, u.username as author_name 
            FROM blog_posts bp 
            LEFT JOIN users u ON bp.author_id = u.id 
            WHERE bp.status = ? 
            ORDER BY bp.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        return fetchAll($sql, [$status, $limit, $offset]);
    }
    
    return fetchAll($sql, [$status]);
}

function getBlogPost($slug) {
    $sql = "SELECT bp.*, u.username as author_name 
            FROM blog_posts bp 
            LEFT JOIN users u ON bp.author_id = u.id 
            WHERE bp.slug = ? AND bp.status = 'published'";
    return fetchOne($sql, [$slug]);
}

function incrementPostViews($postId) {
    $sql = "UPDATE blog_posts SET views = views + 1 WHERE id = ?";
    executeQuery($sql, [$postId]);
}

// Contact functions
function saveContactMessage($data) {
    $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    executeQuery($sql, [
        $data['name'],
        $data['email'],
        $data['phone'] ?? null,
        $data['subject'],
        $data['message'],
        $ip
    ]);
    
    return getLastInsertId();
}

function getContactMessages($status = null, $limit = null) {
    $sql = "SELECT * FROM contact_messages";
    $params = [];
    
    if ($status) {
        $sql .= " WHERE status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    return fetchAll($sql, $params);
}

// Settings functions
function getSetting($key, $default = null) {
    $sql = "SELECT setting_value FROM site_settings WHERE setting_key = ?";
    $result = fetchOne($sql, [$key]);
    return $result ? $result['setting_value'] : $default;
}

function setSetting($key, $value, $type = 'text', $description = '') {
    $sql = "INSERT INTO site_settings (setting_key, setting_value, setting_type, description) 
            VALUES (?, ?, ?, ?) 
            ON CONFLICT (setting_key) 
            DO UPDATE SET 
                setting_value = EXCLUDED.setting_value,
                setting_type = EXCLUDED.setting_type,
                description = EXCLUDED.description,
                updated_at = CURRENT_TIMESTAMP";
    
    executeQuery($sql, [$key, $value, $type, $description]);
}

// Search function
function searchBlogPosts($query, $limit = 10) {
    $sql = "SELECT bp.*, u.username as author_name,
                   ts_rank(to_tsvector('portuguese', bp.title || ' ' || bp.excerpt || ' ' || bp.content), 
                          plainto_tsquery('portuguese', ?)) as rank
            FROM blog_posts bp 
            LEFT JOIN users u ON bp.author_id = u.id 
            WHERE bp.status = 'published' 
            AND (bp.title ILIKE ? OR bp.excerpt ILIKE ? OR bp.content ILIKE ? OR bp.tags ILIKE ?)
            ORDER BY rank DESC, bp.created_at DESC 
            LIMIT ?";
    
    $searchTerm = '%' . $query . '%';
    return fetchAll($sql, [$query, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
}

// Utility functions
function generateSlug($text) {
    // Remove special characters and convert to lowercase
    $slug = strtolower(trim($text));
    
    // Replace common Portuguese characters
    $slug = str_replace(
        ['ã', 'á', 'à', 'â', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'õ', 'ó', 'ò', 'ô', 'ö', 'ú', 'ù', 'û', 'ü', 'ç', 'ñ'],
        ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c', 'n'],
        $slug
    );
    
    // Replace non-alphanumeric characters with hyphens
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    
    // Remove leading/trailing hyphens
    $slug = trim($slug, '-');
    
    return $slug;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit();
    }
}

function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $sql = "SELECT * FROM users WHERE id = ?";
    return fetchOne($sql, [$_SESSION['user_id']]);
}

// Email function
function sendEmail($to, $subject, $message, $headers = []) {
    $default_headers = [
        'From' => getSetting('contact_email', 'santosjoyce56@gmail.com'),
        'Reply-To' => getSetting('contact_email', 'santosjoyce56@gmail.com'),
        'X-Mailer' => 'PHP/' . phpversion(),
        'Content-Type' => 'text/html; charset=UTF-8'
    ];
    
    $headers = array_merge($default_headers, $headers);
    $headers_string = '';
    
    foreach ($headers as $key => $value) {
        $headers_string .= "$key: $value\r\n";
    }
    
    return mail($to, $subject, $message, $headers_string);
}

// Statistics functions
function getDashboardStats() {
    $stats = [];
    
    // Total blog posts
    $stats['total_posts'] = fetchOne("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'")['count'];
    
    // Total messages
    $stats['total_messages'] = fetchOne("SELECT COUNT(*) as count FROM contact_messages")['count'];
    
    // New messages
    $stats['new_messages'] = fetchOne("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'")['count'];
    
    // Total views
    $stats['total_views'] = fetchOne("SELECT SUM(views) as total FROM blog_posts")['total'] ?: 0;
    
    return $stats;
}

// Initialize database on first load
try {
    initializeDatabase();
} catch (Exception $e) {
    error_log("Failed to initialize database: " . $e->getMessage());
}
?>
