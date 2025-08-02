<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
requireLogin();

$currentUser = getCurrentUser();

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $uploadDir = '../assets/images/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    $file = $_FILES['media_file'];
    $errors = [];
    
    // Validation
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erro no upload do arquivo.';
    } elseif ($file['size'] > $maxFileSize) {
        $errors[] = 'Arquivo muito grande. Máximo 5MB.';
    } elseif (!in_array($file['type'], $allowedTypes)) {
        $errors[] = 'Tipo de arquivo não permitido. Use JPEG, PNG, GIF ou WebP.';
    }
    
    if (empty($errors)) {
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $_SESSION['success'] = 'Arquivo enviado com sucesso!';
            // You could save file info to database here if needed
        } else {
            $errors[] = 'Falha ao salvar arquivo.';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode(' ', $errors);
    }
    
    header('Location: media.php');
    exit();
}

// Handle file deletion
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['filename'])) {
    $filename = $_POST['filename'];
    $filepath = '../assets/images/' . $filename;
    
    if (file_exists($filepath) && is_file($filepath)) {
        if (unlink($filepath)) {
            $_SESSION['success'] = 'Arquivo excluído com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao excluir arquivo.';
        }
    } else {
        $_SESSION['error'] = 'Arquivo não encontrado.';
    }
    
    header('Location: media.php');
    exit();
}

// Get media files
$mediaDir = '../assets/images/';
$mediaFiles = [];

if (is_dir($mediaDir)) {
    $files = array_diff(scandir($mediaDir), ['.', '..', 'README.md']);
    
    foreach ($files as $file) {
        $filepath = $mediaDir . $file;
        
        if (is_file($filepath)) {
            $fileInfo = [
                'name' => $file,
                'size' => filesize($filepath),
                'modified' => filemtime($filepath),
                'type' => mime_content_type($filepath),
                'url' => '../assets/images/' . $file
            ];
            
            // Only include image files
            if (strpos($fileInfo['type'], 'image/') === 0) {
                $mediaFiles[] = $fileInfo;
            }
        }
    }
    
    // Sort by modification date (newest first)
    usort($mediaFiles, function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca de Mídia - Joyce Santos</title>
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
                        <a href="blog-new.php" class="nav-link">
                            <i class="fas fa-plus"></i>
                            Novo Post
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="media.php" class="nav-link active">
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
                <h1>Biblioteca de Mídia</h1>
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
                    <h2 class="content-title">Arquivos de Mídia</h2>
                    <p class="content-subtitle">Gerencie imagens e outros arquivos do site</p>
                </div>
                <button class="btn btn-primary" onclick="openUploadModal()">
                    <i class="fas fa-plus"></i>
                    Enviar Arquivo
                </button>
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
            
            <!-- Media Grid -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Arquivos (<?php echo count($mediaFiles); ?>)</h3>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-outline" onclick="toggleView()">
                            <i class="fas fa-th" id="viewToggleIcon"></i>
                            <span id="viewToggleText">Grade</span>
                        </button>
                    </div>
                </div>
                
                <?php if (empty($mediaFiles)): ?>
                    <div class="card-body">
                        <div class="empty-state">
                            <i class="fas fa-images"></i>
                            <h3>Nenhum arquivo encontrado</h3>
                            <p>Envie seu primeiro arquivo de mídia para começar.</p>
                            <button class="btn btn-primary" onclick="openUploadModal()">
                                <i class="fas fa-plus"></i>
                                Enviar Arquivo
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="media-container" id="mediaContainer">
                        <div class="media-grid" id="mediaGrid">
                            <?php foreach ($mediaFiles as $file): ?>
                                <div class="media-item" data-filename="<?php echo htmlspecialchars($file['name']); ?>">
                                    <div class="media-thumbnail">
                                        <img src="<?php echo htmlspecialchars($file['url']); ?>" 
                                             alt="<?php echo htmlspecialchars($file['name']); ?>"
                                             onclick="openImageModal('<?php echo htmlspecialchars($file['url']); ?>', '<?php echo htmlspecialchars($file['name']); ?>')">
                                    </div>
                                    <div class="media-info">
                                        <div class="media-name" title="<?php echo htmlspecialchars($file['name']); ?>">
                                            <?php echo htmlspecialchars($file['name']); ?>
                                        </div>
                                        <div class="media-details">
                                            <span class="media-size"><?php echo formatFileSize($file['size']); ?></span>
                                            <span class="media-date"><?php echo date('d/m/Y', $file['modified']); ?></span>
                                        </div>
                                    </div>
                                    <div class="media-actions">
                                        <button class="action-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($file['url']); ?>')" title="Copiar URL">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button class="action-btn action-btn-danger" onclick="deleteFile('<?php echo htmlspecialchars($file['name']); ?>')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Enviar Arquivo</h3>
                <button class="modal-close" onclick="closeUploadModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="modal-body">
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">
                            <p><strong>Clique para selecionar</strong> ou arraste arquivos aqui</p>
                            <p class="upload-hint">JPEG, PNG, GIF, WebP até 5MB</p>
                        </div>
                        <input type="file" name="media_file" id="mediaFile" accept="image/*" onchange="handleFileSelect(this)">
                    </div>
                    
                    <div id="filePreview" class="file-preview" style="display: none;">
                        <img id="previewImage" src="" alt="Preview">
                        <div class="preview-info">
                            <div id="fileName"></div>
                            <div id="fileSize"></div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeUploadModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                        <i class="fas fa-upload"></i>
                        Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3 id="imageModalTitle">Visualizar Imagem</h3>
                <button class="modal-close" onclick="closeImageModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="image-preview-large">
                    <img id="modalImage" src="" alt="">
                </div>
                <div class="image-actions">
                    <button class="btn btn-outline" onclick="copyToClipboard(document.getElementById('modalImage').src)">
                        <i class="fas fa-copy"></i>
                        Copiar URL
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete confirmation form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="filename" id="deleteFilename">
    </form>
    
    <script>
        let currentView = 'grid';
        
        // Upload Modal Functions
        function openUploadModal() {
            document.getElementById('uploadModal').style.display = 'flex';
        }
        
        function closeUploadModal() {
            document.getElementById('uploadModal').style.display = 'none';
            document.getElementById('mediaFile').value = '';
            document.getElementById('filePreview').style.display = 'none';
            document.getElementById('uploadBtn').disabled = true;
        }
        
        // Image Modal Functions
        function openImageModal(src, filename) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModalTitle').textContent = filename;
            document.getElementById('imageModal').style.display = 'flex';
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        
        // File Upload Functions
        function handleFileSelect(input) {
            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImage').src = e.target.result;
                    document.getElementById('fileName').textContent = file.name;
                    document.getElementById('fileSize').textContent = formatFileSize(file.size);
                    document.getElementById('filePreview').style.display = 'block';
                    document.getElementById('uploadBtn').disabled = false;
                };
                reader.readAsDataURL(file);
            }
        }
        
        function formatFileSize(bytes) {
            if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            } else {
                return bytes + ' B';
            }
        }
        
        // Drag and Drop
        const uploadArea = document.getElementById('uploadArea');
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('mediaFile').files = files;
                handleFileSelect(document.getElementById('mediaFile'));
            }
        });
        
        // View Toggle
        function toggleView() {
            const grid = document.getElementById('mediaGrid');
            const icon = document.getElementById('viewToggleIcon');
            const text = document.getElementById('viewToggleText');
            
            if (currentView === 'grid') {
                grid.classList.add('media-list');
                icon.className = 'fas fa-list';
                text.textContent = 'Lista';
                currentView = 'list';
            } else {
                grid.classList.remove('media-list');
                icon.className = 'fas fa-th';
                text.textContent = 'Grade';
                currentView = 'grid';
            }
        }
        
        // Copy to Clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show temporary success message
                const originalText = event.target.innerHTML;
                event.target.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    event.target.innerHTML = originalText;
                }, 1000);
            });
        }
        
        // Delete File
        function deleteFile(filename) {
            if (confirm('Tem certeza que deseja excluir este arquivo? Esta ação não pode ser desfeita.')) {
                document.getElementById('deleteFilename').value = filename;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Close modals on outside click
        window.onclick = function(event) {
            const uploadModal = document.getElementById('uploadModal');
            const imageModal = document.getElementById('imageModal');
            
            if (event.target === uploadModal) {
                closeUploadModal();
            } else if (event.target === imageModal) {
                closeImageModal();
            }
        }
    </script>
    
    <style>
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1rem;
        }
        
        .media-grid.media-list {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        
        .media-item {
            background: white;
            border: 1px solid var(--admin-border);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.2s;
            position: relative;
        }
        
        .media-item:hover {
            box-shadow: 0 4px 12px var(--admin-shadow);
            transform: translateY(-2px);
        }
        
        .media-list .media-item {
            display: flex;
            align-items: center;
            padding: 0.5rem;
        }
        
        .media-thumbnail {
            aspect-ratio: 1;
            overflow: hidden;
            background: var(--admin-bg);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .media-list .media-thumbnail {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
            margin-right: 1rem;
            border-radius: 4px;
        }
        
        .media-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .media-thumbnail img:hover {
            transform: scale(1.05);
        }
        
        .media-info {
            padding: 0.75rem;
            flex: 1;
        }
        
        .media-list .media-info {
            padding: 0;
        }
        
        .media-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .media-details {
            font-size: 0.875rem;
            color: var(--admin-gray);
            display: flex;
            justify-content: space-between;
        }
        
        .media-list .media-details {
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .media-actions {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            display: flex;
            gap: 0.25rem;
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .media-item:hover .media-actions {
            opacity: 1;
        }
        
        .media-list .media-actions {
            position: static;
            opacity: 1;
            margin-left: auto;
        }
        
        .action-btn {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--admin-gray);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background: white;
            color: var(--admin-primary);
        }
        
        .action-btn-danger:hover {
            color: var(--admin-danger);
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
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .modal-large {
            max-width: 800px;
        }
        
        .modal-header {
            padding: 1rem;
            border-bottom: 1px solid var(--admin-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--admin-gray);
            cursor: pointer;
        }
        
        .modal-body {
            padding: 1rem;
            flex: 1;
            overflow-y: auto;
        }
        
        .modal-footer {
            padding: 1rem;
            border-top: 1px solid var(--admin-border);
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        
        /* Upload Area */
        .upload-area {
            border: 2px dashed var(--admin-border);
            border-radius: 8px;
            padding: 3rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .upload-area:hover,
        .upload-area.drag-over {
            border-color: var(--admin-primary);
            background: rgba(114, 47, 55, 0.05);
        }
        
        .upload-area input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: var(--admin-primary);
            margin-bottom: 1rem;
        }
        
        .upload-text p {
            margin: 0.5rem 0;
        }
        
        .upload-hint {
            font-size: 0.875rem;
            color: var(--admin-gray);
        }
        
        .file-preview {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            padding: 1rem;
            background: var(--admin-bg);
            border-radius: 8px;
        }
        
        .file-preview img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .preview-info div {
            margin-bottom: 0.25rem;
        }
        
        .image-preview-large {
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .image-preview-large img {
            max-width: 100%;
            max-height: 60vh;
            border-radius: 8px;
        }
        
        .image-actions {
            text-align: center;
        }
        
        .card-actions {
            display: flex;
            gap: 0.5rem;
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
