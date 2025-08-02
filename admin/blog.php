<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$currentUser = getCurrentUser();

// Handle delete action
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['post_id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
        $stmt->execute([$_POST['post_id']]);
        $_SESSION['success'] = 'Post excluído com sucesso!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erro ao excluir post: ' . $e->getMessage();
    }
    header('Location: blog.php');
    exit();
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['selected_posts'])) {
    $action = $_POST['bulk_action'];
    $postIds = $_POST['selected_posts'];
    
    try {
        switch ($action) {
            case 'delete':
                $placeholders = str_repeat('?,', count($postIds) - 1) . '?';
                $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id IN ($placeholders)");
                $stmt->execute($postIds);
                $_SESSION['success'] = count($postIds) . ' posts excluídos com sucesso!';
                break;
                
            case 'publish':
                $placeholders = str_repeat('?,', count($postIds) - 1) . '?';
                $stmt = $pdo->prepare("UPDATE blog_posts SET status = 'published' WHERE id IN ($placeholders)");
                $stmt->execute($postIds);
                $_SESSION['success'] = count($postIds) . ' posts publicados com sucesso!';
                break;
                
            case 'draft':
                $placeholders = str_repeat('?,', count($postIds) - 1) . '?';
                $stmt = $pdo->prepare("UPDATE blog_posts SET status = 'draft' WHERE id IN ($placeholders)");
                $stmt->execute($postIds);
                $_SESSION['success'] = count($postIds) . ' posts movidos para rascunho!';
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Erro ao executar ação: ' . $e->getMessage();
    }
    
    header('Location: blog.php');
    exit();
}

// Pagination and filtering
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$whereConditions = [];
$params = [];

if ($status !== 'all') {
    $whereConditions[] = "status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $whereConditions[] = "(title ILIKE ? OR content ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get total count
$countSql = "SELECT COUNT(*) FROM blog_posts $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalPosts = $stmt->fetchColumn();
$totalPages = ceil($totalPosts / $perPage);

// Get posts
$sql = "
    SELECT id, title, slug, status, created_at, updated_at, views
    FROM blog_posts 
    $whereClause
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
";

$params[] = $perPage;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get statistics
$stats = [];
$statuses = ['published', 'draft'];
foreach ($statuses as $stat) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE status = ?");
    $stmt->execute([$stat]);
    $stats[$stat] = $stmt->fetchColumn();
}

$stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
$stats['total'] = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Blog - Joyce Santos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="Joyce Santos Logo" class="sidebar-logo">
                <h3 class="sidebar-title">Painel Admin</h3>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Principal</div>
                    <div class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Conteúdo</div>
                    <div class="nav-item">
                        <a href="blog.php" class="nav-link active">
                            <i class="fas fa-blog"></i>
                            Blog
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="blog-new.php" class="nav-link">
                            <i class="fas fa-plus"></i>
                            Novo Post
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="media.php" class="nav-link">
                            <i class="fas fa-images"></i>
                            Mídia
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Comunicação</div>
                    <div class="nav-item">
                        <a href="messages.php" class="nav-link">
                            <i class="fas fa-envelope"></i>
                            Mensagens
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Configurações</div>
                    <div class="nav-item">
                        <a href="settings.php" class="nav-link">
                            <i class="fas fa-cog"></i>
                            Site
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="profile.php" class="nav-link">
                            <i class="fas fa-user"></i>
                            Perfil
                        </a>
                    </div>
                </div>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-content">
            <!-- Header -->
            <header class="admin-header">
                <h1>Gerenciar Blog</h1>
                <div class="admin-user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($currentUser['username']); ?></div>
                        <div class="user-role"><?php echo ucfirst($currentUser['role']); ?></div>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            </header>
            
            <!-- Content -->
            <div class="content-header">
                <div>
                    <h2 class="content-title">Posts do Blog</h2>
                    <p class="content-subtitle">Gerencie todos os posts do seu blog</p>
                </div>
                <a href="blog-new.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Novo Post
                </a>
            </div>
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics -->
            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $stats['total']; ?></span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $stats['published']; ?></span>
                    <span class="stat-label">Publicados</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $stats['draft']; ?></span>
                    <span class="stat-label">Rascunhos</span>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="filters-form">
                        <div class="filters-row">
                            <div class="filter-group">
                                <select name="status" class="form-select">
                                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Todos os Status</option>
                                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Publicados</option>
                                    <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Rascunhos</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <input type="text" name="search" placeholder="Buscar posts..." value="<?php echo htmlspecialchars($search); ?>" class="form-input">
                            </div>
                            <div class="filter-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                    Filtrar
                                </button>
                                <a href="blog.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                    Limpar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Posts Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Posts (<?php echo $totalPosts; ?>)</h3>
                </div>
                
                <?php if (empty($posts)): ?>
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="fas fa-blog"></i>
                            <h3>Nenhum post encontrado</h3>
                            <p>
                                <?php if (!empty($search) || $status !== 'all'): ?>
                                    Nenhum post corresponde aos filtros aplicados.
                                <?php else: ?>
                                    Você ainda não criou nenhum post. Comece criando seu primeiro post!
                                <?php endif; ?>
                            </p>
                            <a href="blog-new.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Criar Primeiro Post
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" id="bulkForm">
                        <div class="table-header">
                            <div class="bulk-actions">
                                <select name="bulk_action" class="form-select">
                                    <option value="">Ações em massa</option>
                                    <option value="publish">Publicar</option>
                                    <option value="draft">Mover para rascunho</option>
                                    <option value="delete">Excluir</option>
                                </select>
                                <button type="submit" class="btn btn-outline" onclick="return confirmBulkAction()">
                                    Aplicar
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>Título</th>
                                        <th>Status</th>
                                        <th>Visualizações</th>
                                        <th>Data</th>
                                        <th width="120">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_posts[]" value="<?php echo $post['id']; ?>" class="post-checkbox">
                                            </td>
                                            <td>
                                                <div class="post-title">
                                                    <a href="blog-edit.php?id=<?php echo $post['id']; ?>" class="post-link">
                                                        <?php echo htmlspecialchars($post['title']); ?>
                                                    </a>
                                                    <div class="post-actions-row">
                                                        <a href="blog-edit.php?id=<?php echo $post['id']; ?>">Editar</a>
                                                        <span class="separator">|</span>
                                                        <a href="../blog.html?post=<?php echo $post['slug']; ?>" target="_blank">Ver</a>
                                                        <span class="separator">|</span>
                                                        <a href="#" onclick="deletePost(<?php echo $post['id']; ?>)" class="text-danger">Excluir</a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                    <?php echo $post['status'] === 'published' ? 'Publicado' : 'Rascunho'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($post['views']); ?></td>
                                            <td>
                                                <div class="date-info">
                                                    <div><?php echo formatDate($post['created_at']); ?></div>
                                                    <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                                        <small>Editado: <?php echo formatDate($post['updated_at']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="blog-edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="../blog.html?post=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-sm btn-outline" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination-wrapper">
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-btn">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" 
                                       class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-btn">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="pagination-info">
                                Mostrando <?php echo $offset + 1; ?>-<?php echo min($offset + $perPage, $totalPosts); ?> de <?php echo $totalPosts; ?> posts
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Delete confirmation form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="post_id" id="deletePostId">
    </form>
    
    <script>
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.post-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Delete post function
        function deletePost(postId) {
            if (confirm('Tem certeza que deseja excluir este post? Esta ação não pode ser desfeita.')) {
                document.getElementById('deletePostId').value = postId;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Bulk actions confirmation
        function confirmBulkAction() {
            const action = document.querySelector('select[name="bulk_action"]').value;
            const selectedPosts = document.querySelectorAll('.post-checkbox:checked');
            
            if (!action) {
                alert('Selecione uma ação para continuar.');
                return false;
            }
            
            if (selectedPosts.length === 0) {
                alert('Selecione pelo menos um post para continuar.');
                return false;
            }
            
            let message = '';
            switch (action) {
                case 'delete':
                    message = `Tem certeza que deseja excluir ${selectedPosts.length} post(s)? Esta ação não pode ser desfeita.`;
                    break;
                case 'publish':
                    message = `Tem certeza que deseja publicar ${selectedPosts.length} post(s)?`;
                    break;
                case 'draft':
                    message = `Tem certeza que deseja mover ${selectedPosts.length} post(s) para rascunho?`;
                    break;
            }
            
            return confirm(message);
        }
    </script>
    
    <style>
        .stats-row {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid var(--admin-border);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--admin-primary);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--admin-gray);
        }
        
        .filters-form {
            margin: 0;
        }
        
        .filters-row {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .filter-group {
            flex: 1;
        }
        
        .filter-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .table-header {
            padding: 1rem;
            border-bottom: 1px solid var(--admin-border);
        }
        
        .bulk-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .post-title .post-link {
            color: var(--admin-primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .post-title .post-link:hover {
            text-decoration: underline;
        }
        
        .post-actions-row {
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }
        
        .post-actions-row a {
            color: var(--admin-gray);
            text-decoration: none;
        }
        
        .post-actions-row a:hover {
            color: var(--admin-primary);
        }
        
        .post-actions-row .text-danger:hover {
            color: var(--admin-danger) !important;
        }
        
        .separator {
            margin: 0 0.5rem;
            color: var(--admin-border);
        }
        
        .date-info small {
            color: var(--admin-gray);
            display: block;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.25rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--admin-gray);
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: var(--admin-dark);
        }
        
        .empty-state p {
            color: var(--admin-gray);
            margin-bottom: 2rem;
        }
        
        .pagination-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-top: 1px solid var(--admin-border);
        }
        
        .pagination {
            display: flex;
            gap: 0.25rem;
        }
        
        .page-btn {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--admin-border);
            background: white;
            color: var(--admin-dark);
            text-decoration: none;
            border-radius: 4px;
        }
        
        .page-btn:hover,
        .page-btn.active {
            background: var(--admin-primary);
            color: white;
            border-color: var(--admin-primary);
        }
        
        .pagination-info {
            font-size: 0.875rem;
            color: var(--admin-gray);
        }
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-success {
            background: var(--admin-success);
            color: white;
        }
        
        .badge-warning {
            background: var(--admin-warning);
            color: #212529;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</body>
</html>
