<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGetPosts();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log("Blog API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

function handleGetPosts() {
    global $pdo;
    
    // Get query parameters
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $featured = isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : null;
    
    // Build query
    $sql = "SELECT 
                id, 
                title, 
                slug, 
                excerpt, 
                image, 
                category, 
                tags, 
                featured, 
                views, 
                created_at,
                updated_at
            FROM blog_posts 
            WHERE status = 'published'";
    
    $params = [];
    
    if ($category) {
        $sql .= " AND category = :category";
        $params['category'] = $category;
    }
    
    if ($featured !== null) {
        $sql .= " AND featured = :featured";
        $params['featured'] = $featured;
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $params['limit'] = $limit;
    $params['offset'] = $offset;
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters with explicit types
    foreach ($params as $key => $value) {
        if ($key === 'limit' || $key === 'offset') {
            $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
        } elseif ($key === 'featured') {
            $stmt->bindValue(":$key", $value, PDO::PARAM_BOOL);
        } else {
            $stmt->bindValue(":$key", $value, PDO::PARAM_STR);
        }
    }
    
    $stmt->execute();
    $posts = $stmt->fetchAll();
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total FROM blog_posts WHERE status = 'published'";
    $countParams = [];
    
    if ($category) {
        $countSql .= " AND category = :category";
        $countParams['category'] = $category;
    }
    
    if ($featured !== null) {
        $countSql .= " AND featured = :featured";
        $countParams['featured'] = $featured;
    }
    
    $countStmt = $pdo->prepare($countSql);
    foreach ($countParams as $key => $value) {
        if ($key === 'featured') {
            $countStmt->bindValue(":$key", $value, PDO::PARAM_BOOL);
        } else {
            $countStmt->bindValue(":$key", $value, PDO::PARAM_STR);
        }
    }
    $countStmt->execute();
    $total = $countStmt->fetch()['total'];
    
    // Format the posts
    $formattedPosts = [];
    foreach ($posts as $post) {
        $formattedPosts[] = [
            'id' => $post['id'],
            'title' => $post['title'],
            'slug' => $post['slug'],
            'excerpt' => $post['excerpt'],
            'image' => $post['image'],
            'category' => $post['category'],
            'tags' => $post['tags'] ? explode(',', $post['tags']) : [],
            'featured' => (bool)$post['featured'],
            'views' => (int)$post['views'],
            'created_at' => $post['created_at'],
            'updated_at' => $post['updated_at'],
            'url' => "blog.html?post=" . $post['slug']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'posts' => $formattedPosts,
        'pagination' => [
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset,
            'has_more' => ($offset + $limit) < $total
        ]
    ], JSON_PRETTY_PRINT);
}
?>
