<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$currentUser = getCurrentUser();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $excerpt = trim($_POST['excerpt']);
    $status = $_POST['status'];
    $featured_image = trim($_POST['featured_image']);
    $meta_description = trim($_POST['meta_description']);
    $tags = trim($_POST['tags']);
    
    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = 'O título é obrigatório.';
    }
    
    if (empty($content)) {
        $errors[] = 'O conteúdo é obrigatório.';
    }
    
    if (!in_array($status, ['draft', 'published'])) {
        $errors[] = 'Status inválido.';
    }
    
    if (empty($errors)) {
        try {
            // Generate slug
            $slug = generateSlug($title);
            
            // Check if slug already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ?");
            $stmt->execute([$slug]);
            
            if ($stmt->fetchColumn() > 0) {
                $slug .= '-' . time();
            }
            
            // Insert post
            $stmt = $pdo->prepare("
                INSERT INTO blog_posts (
                    title, slug, content, excerpt, status, featured_image, 
                    meta_description, tags, author_id, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([
                $title,
                $slug,
                $content,
                $excerpt,
                $status,
                $featured_image,
                $meta_description,
                $tags,
                $currentUser['id']
            ]);
            
            $postId = $pdo->lastInsertId();
            
            $_SESSION['success'] = 'Post criado com sucesso!';
            header('Location: blog-edit.php?id=' . $postId);
            exit();
            
        } catch (PDOException $e) {
            $errors[] = 'Erro ao salvar post: ' . $e->getMessage();
        }
    }
}

// Helper function to generate slug
function generateSlug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[àáâãäå]/u', 'a', $slug);
    $slug = preg_replace('/[èéêë]/u', 'e', $slug);
    $slug = preg_replace('/[ìíîï]/u', 'i', $slug);
    $slug = preg_replace('/[òóôõö]/u', 'o', $slug);
    $slug = preg_replace('/[ùúûü]/u', 'u', $slug);
    $slug = preg_replace('/[ç]/u', 'c', $slug);
    $slug = preg_replace('/[ñ]/u', 'n', $slug);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Post - Joyce Santos</title>
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
                        <a href="blog.php" class="nav-link">
                            <i class="fas fa-blog"></i>
                            Blog
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="blog-new.php" class="nav-link active">
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
                <h1>Novo Post</h1>
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
                    <h2 class="content-title">Criar Novo Post</h2>
                    <p class="content-subtitle">Adicione um novo post ao seu blog</p>
                </div>
                <div class="header-actions">
                    <a href="blog.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>
            </div>
            
            <!-- Error Messages -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="post-form">
                <div class="form-layout">
                    <!-- Main Content -->
                    <div class="form-main">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="title" class="form-label">Título do Post *</label>
                                    <input type="text" id="title" name="title" class="form-input" 
                                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                                           placeholder="Digite o título do post" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="content" class="form-label">Conteúdo *</label>
                                    <div class="editor-toolbar">
                                        <button type="button" class="toolbar-btn" onclick="formatText('bold')" title="Negrito">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="toolbar-btn" onclick="formatText('italic')" title="Itálico">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="toolbar-btn" onclick="formatText('underline')" title="Sublinhado">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <div class="toolbar-separator"></div>
                                        <button type="button" class="toolbar-btn" onclick="insertList('ul')" title="Lista">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" class="toolbar-btn" onclick="insertList('ol')" title="Lista Numerada">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <div class="toolbar-separator"></div>
                                        <button type="button" class="toolbar-btn" onclick="insertLink()" title="Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <button type="button" class="toolbar-btn" onclick="insertImage()" title="Imagem">
                                            <i class="fas fa-image"></i>
                                        </button>
                                    </div>
                                    <div class="editor-container">
                                        <div id="content-editor" class="content-editor" contenteditable="true" 
                                             placeholder="Escreva o conteúdo do seu post aqui..."><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></div>
                                        <textarea id="content" name="content" style="display: none;" required><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="excerpt" class="form-label">Resumo</label>
                                    <textarea id="excerpt" name="excerpt" class="form-input" rows="3" 
                                              placeholder="Digite um breve resumo do post (opcional)"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ''; ?></textarea>
                                    <small class="form-help">Um resumo breve que aparecerá na listagem de posts</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SEO Section -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">SEO e Configurações</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="meta_description" class="form-label">Meta Descrição</label>
                                    <textarea id="meta_description" name="meta_description" class="form-input" rows="2" 
                                              placeholder="Descrição para motores de busca (opcional)"><?php echo isset($_POST['meta_description']) ? htmlspecialchars($_POST['meta_description']) : ''; ?></textarea>
                                    <small class="form-help">Recomendado: 150-160 caracteres</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="tags" class="form-label">Tags</label>
                                    <input type="text" id="tags" name="tags" class="form-input" 
                                           value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>" 
                                           placeholder="direito, consumidor, advocacia">
                                    <small class="form-help">Separe as tags com vírgulas</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="featured_image" class="form-label">Imagem Destacada</label>
                                    <input type="url" id="featured_image" name="featured_image" class="form-input" 
                                           value="<?php echo isset($_POST['featured_image']) ? htmlspecialchars($_POST['featured_image']) : ''; ?>" 
                                           placeholder="https://exemplo.com/imagem.jpg">
                                    <small class="form-help">URL da imagem que aparecerá como destaque do post</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="form-sidebar">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Publicar</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="status" class="form-label">Status</label>
                                    <select id="status" name="status" class="form-select">
                                        <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] === 'draft') ? 'selected' : ''; ?>>
                                            Rascunho
                                        </option>
                                        <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] === 'published') ? 'selected' : ''; ?>>
                                            Publicado
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-save"></i>
                                        Criar Post
                                    </button>
                                    <a href="blog.php" class="btn btn-outline btn-block">
                                        <i class="fas fa-times"></i>
                                        Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Dicas</h3>
                            </div>
                            <div class="card-body">
                                <div class="tips-list">
                                    <div class="tip-item">
                                        <i class="fas fa-lightbulb"></i>
                                        <span>Use títulos descritivos e atraentes</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-lightbulb"></i>
                                        <span>Adicione imagens para tornar o post mais visual</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-lightbulb"></i>
                                        <span>Escreva um resumo envolvente</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-lightbulb"></i>
                                        <span>Use tags relevantes para melhor organização</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        // Auto-save functionality
        let autoSaveTimer;
        
        function autoSave() {
            // Here you would implement auto-save to drafts
            console.log('Auto-saving...');
        }
        
        // Rich text editor functionality
        function formatText(command, value = null) {
            document.execCommand(command, false, value);
            updateHiddenTextarea();
        }
        
        function insertList(type) {
            if (type === 'ul') {
                formatText('insertUnorderedList');
            } else {
                formatText('insertOrderedList');
            }
        }
        
        function insertLink() {
            const url = prompt('Digite a URL do link:');
            if (url) {
                formatText('createLink', url);
            }
        }
        
        function insertImage() {
            const url = prompt('Digite a URL da imagem:');
            if (url) {
                formatText('insertImage', url);
            }
        }
        
        function updateHiddenTextarea() {
            const editor = document.getElementById('content-editor');
            const textarea = document.getElementById('content');
            textarea.value = editor.innerHTML;
        }
        
        // Update hidden textarea when editor content changes
        document.getElementById('content-editor').addEventListener('input', function() {
            updateHiddenTextarea();
            
            // Reset auto-save timer
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSave, 30000); // Auto-save every 30 seconds
        });
        
        // Initialize editor content
        document.addEventListener('DOMContentLoaded', function() {
            const editor = document.getElementById('content-editor');
            const textarea = document.getElementById('content');
            
            if (textarea.value) {
                editor.innerHTML = textarea.value;
            }
            
            // Auto-generate slug from title
            document.getElementById('title').addEventListener('input', function() {
                // You could add slug preview here
            });
        });
        
        // Form validation
        document.querySelector('.post-form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content-editor').innerText.trim();
            
            if (!title) {
                alert('O título é obrigatório.');
                e.preventDefault();
                return;
            }
            
            if (!content) {
                alert('O conteúdo é obrigatório.');
                e.preventDefault();
                return;
            }
            
            // Update hidden textarea before submit
            updateHiddenTextarea();
        });
    </script>
    
    <style>
        .form-layout {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
        }
        
        .form-main {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .form-sidebar {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .editor-toolbar {
            display: flex;
            gap: 0.25rem;
            padding: 0.5rem;
            border: 1px solid var(--admin-border);
            border-bottom: none;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }
        
        .toolbar-btn {
            padding: 0.5rem;
            border: none;
            background: transparent;
            color: var(--admin-gray);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .toolbar-btn:hover {
            background: white;
            color: var(--admin-primary);
        }
        
        .toolbar-separator {
            width: 1px;
            background: var(--admin-border);
            margin: 0.25rem 0.5rem;
        }
        
        .editor-container {
            position: relative;
        }
        
        .content-editor {
            min-height: 400px;
            padding: 1rem;
            border: 1px solid var(--admin-border);
            border-radius: 0 0 8px 8px;
            background: white;
            outline: none;
            line-height: 1.6;
        }
        
        .content-editor:empty:before {
            content: attr(placeholder);
            color: var(--admin-gray);
        }
        
        .content-editor:focus {
            border-color: var(--admin-primary);
        }
        
        .form-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn-block {
            width: 100%;
        }
        
        .tips-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .tip-item {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.875rem;
        }
        
        .tip-item i {
            color: var(--admin-warning);
            margin-top: 0.125rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .header-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        @media (max-width: 1024px) {
            .form-layout {
                grid-template-columns: 1fr;
            }
            
            .form-sidebar {
                order: -1;
            }
        }
    </style>
</body>
</html>
