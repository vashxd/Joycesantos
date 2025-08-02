<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$currentUser = getCurrentUser();

// Get dashboard statistics
try {
    // Get blog posts count
    $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
    $publishedPosts = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'draft'");
    $draftPosts = $stmt->fetchColumn();
    
    // Get contact messages count
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
    $newMessages = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
    $totalMessages = $stmt->fetchColumn();
    
    // Get recent blog posts
    $stmt = $pdo->query("
        SELECT id, title, status, created_at, views 
        FROM blog_posts 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recentPosts = $stmt->fetchAll();
    
    // Get recent messages
    $stmt = $pdo->query("
        SELECT id, name, email, subject, created_at, status 
        FROM contact_messages 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recentMessages = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $publishedPosts = $draftPosts = $newMessages = $totalMessages = 0;
    $recentPosts = $recentMessages = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Joyce Santos</title>
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
                        <a href="index.php" class="nav-link active">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Conteúdo</div>
                    <div class="nav-item">
                        <a href="blog.php" class="nav-link">
                            <i class="fas fa-blog"></i>
                            Blog
                            <?php if ($draftPosts > 0): ?>
                                <span class="nav-badge"><?php echo $draftPosts; ?></span>
                            <?php endif; ?>
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
                            <?php if ($newMessages > 0): ?>
                                <span class="nav-badge"><?php echo $newMessages; ?></span>
                            <?php endif; ?>
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
                <h1>Dashboard</h1>
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
                <h2 class="content-title">Bem-vinda, Joyce!</h2>
                <p class="content-subtitle">Aqui está um resumo da atividade do seu site</p>
            </div>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-blog"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $publishedPosts; ?></h3>
                        <p>Posts Publicados</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $draftPosts; ?></h3>
                        <p>Rascunhos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $newMessages; ?></h3>
                        <p>Mensagens Novas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $totalMessages; ?></h3>
                        <p>Total de Contatos</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Recent Posts -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Posts Recentes</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentPosts)): ?>
                            <p class="text-center" style="color: var(--admin-gray); padding: 2rem;">
                                <i class="fas fa-blog" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                Nenhum post encontrado.<br>
                                <a href="blog-new.php" class="btn btn-primary btn-sm" style="margin-top: 1rem;">
                                    <i class="fas fa-plus"></i>
                                    Criar primeiro post
                                </a>
                            </p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Status</th>
                                            <th>Visualizações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentPosts as $post): ?>
                                            <tr>
                                                <td>
                                                    <a href="blog-edit.php?id=<?php echo $post['id']; ?>" style="color: var(--admin-primary); text-decoration: none;">
                                                        <?php echo htmlspecialchars($post['title']); ?>
                                                    </a>
                                                    <br>
                                                    <small style="color: var(--admin-gray);">
                                                        <?php echo formatDate($post['created_at']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                                        <?php echo $post['status'] === 'published' ? 'Publicado' : 'Rascunho'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $post['views']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center" style="margin-top: 1rem;">
                                <a href="blog.php" class="btn btn-outline">Ver Todos os Posts</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Messages -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Mensagens Recentes</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentMessages)): ?>
                            <p class="text-center" style="color: var(--admin-gray); padding: 2rem;">
                                <i class="fas fa-envelope" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                                Nenhuma mensagem ainda.
                            </p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Remetente</th>
                                            <th>Assunto</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentMessages as $message): ?>
                                            <tr>
                                                <td>
                                                    <a href="message-view.php?id=<?php echo $message['id']; ?>" style="color: var(--admin-primary); text-decoration: none;">
                                                        <?php echo htmlspecialchars($message['name']); ?>
                                                    </a>
                                                    <br>
                                                    <small style="color: var(--admin-gray);">
                                                        <?php echo htmlspecialchars($message['email']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($message['subject']); ?>
                                                    <br>
                                                    <small style="color: var(--admin-gray);">
                                                        <?php echo formatDate($message['created_at']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $message['status'] === 'new' ? 'danger' : 'success'; ?>">
                                                        <?php 
                                                        switch($message['status']) {
                                                            case 'new': echo 'Nova'; break;
                                                            case 'read': echo 'Lida'; break;
                                                            case 'replied': echo 'Respondida'; break;
                                                            default: echo 'Arquivada';
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center" style="margin-top: 1rem;">
                                <a href="messages.php" class="btn btn-outline">Ver Todas as Mensagens</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">Ações Rápidas</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <a href="blog-new.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Novo Post
                        </a>
                        <a href="messages.php" class="btn btn-success">
                            <i class="fas fa-envelope"></i>
                            Ver Mensagens
                        </a>
                        <a href="settings.php" class="btn btn-warning">
                            <i class="fas fa-cog"></i>
                            Configurações
                        </a>
                        <a href="../index.html" class="btn btn-outline" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                            Ver Site
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <style>
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
    
    .badge-danger {
        background: var(--admin-danger);
        color: white;
    }
    </style>
</body>
</html>
