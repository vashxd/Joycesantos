<?php
session_start();

// Verificar se está logado
function isLoggedIn() {
    return isset($_SESSION['user_logged']) && $_SESSION['user_logged'] === true;
}

if (!isLoggedIn()) {
    header('Location: login_simple.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Joyce Santos';
$user_email = $_SESSION['user_email'] ?? 'santosjoyce56@gmail.com';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Joyce Santos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }
        
        .sidebar {
            width: 280px;
            background: #722F37;
            color: white;
            padding: 15px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: transform 0.3s ease;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        .sidebar-header {
            text-align: center;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            font-family: 'Playfair Display', serif;
            margin-top: 10px;
            font-size: 18px;
        }
        
        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .user-info h4 {
            margin-bottom: 5px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .user-info p {
            font-size: 12px;
            opacity: 0.8;
            word-break: break-word;
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 8px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 12px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }
        
        .nav-link i {
            margin-right: 12px;
            width: 18px;
            text-align: center;
            font-size: 14px;
        }
        
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #722F37;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 20px;
            width: calc(100% - 280px);
            min-height: 100vh;
        }
        
        .header {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header h1 {
            color: #722F37;
            font-family: 'Playfair Display', serif;
            font-size: 24px;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
            white-space: nowrap;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .dashboard-card h3 {
            color: #722F37;
            margin-bottom: 15px;
            font-family: 'Playfair Display', serif;
            font-size: 18px;
        }
        
        .dashboard-card .icon {
            font-size: 42px;
            color: #722F37;
            margin-bottom: 15px;
        }
        
        .dashboard-card p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .card-btn {
            background: #722F37;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }
        
        .card-btn:hover {
            background: #5a252b;
        }
        
        .welcome-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .welcome-section h2 {
            color: #722F37;
            margin-bottom: 15px;
            font-family: 'Playfair Display', serif;
            font-size: 22px;
        }
        
        .instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #722F37;
            margin-top: 15px;
        }
        
        .instructions h4 {
            color: #722F37;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .instructions ul {
            list-style-position: inside;
            color: #666;
        }
        
        .instructions li {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .instructions code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .status-alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 80px 15px 20px;
            }
            
            .header {
                padding: 15px;
                margin-top: 0;
            }
            
            .header h1 {
                font-size: 20px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .dashboard-card {
                padding: 20px;
            }
            
            .welcome-section {
                padding: 20px;
            }
            
            .welcome-section h2 {
                font-size: 18px;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 80px 10px 15px;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .dashboard-card {
                padding: 15px;
            }
            
            .dashboard-card .icon {
                font-size: 36px;
            }
            
            .dashboard-card h3 {
                font-size: 16px;
            }
            
            .dashboard-card p {
                font-size: 13px;
            }
            
            .welcome-section {
                padding: 15px;
            }
            
            .instructions {
                padding: 15px;
            }
        }
        
        /* Tablets */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar {
                width: 260px;
            }
            
            .main-content {
                margin-left: 260px;
                width: calc(100% - 260px);
            }
            
            .dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
    </style>
</head>
<body>
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    
    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="fas fa-gavel" style="color: white; font-size: 20px;"></i>
                </div>
                <h3>Painel Admin</h3>
            </div>
            
            <div class="user-info">
                <h4><?php echo htmlspecialchars($user_name); ?></h4>
                <p><?php echo htmlspecialchars($user_email); ?></p>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-blog"></i>
                        Blog Posts
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-envelope"></i>
                        Mensagens
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-cog"></i>
                        Configurações
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../index.html" class="nav-link">
                        <i class="fas fa-external-link-alt"></i>
                        Ver Site
                    </a>
                </li>
            </ul>
        </aside>
        
        <main class="main-content">
            <header class="header">
                <h1>Dashboard</h1>
                <a href="login_simple.php?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Sair
                </a>
            </header>
            
            <div class="status-alert">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Modo Demonstração:</strong> 
                    Este é um painel temporário que funciona sem banco de dados. Para funcionalidade completa, configure o MySQL.
                </div>
            </div>
            
            <div class="welcome-section">
                <h2>Bem-vinda, <?php echo htmlspecialchars($user_name); ?>! 🎉</h2>
                <p>Este é seu painel administrativo onde você pode gerenciar todo o conteúdo do seu site.</p>
                
                <div class="instructions">
                    <h4>📋 Próximos Passos:</h4>
                    <ul>
                        <li>Configure o MySQL para funcionalidade completa</li>
                        <li>Adicione sua foto em <code>assets/images/about-placeholder.jpg</code></li>
                        <li>Crie seu primeiro post no blog</li>
                        <li>Personalize as informações de contato</li>
                        <li>Configure as redes sociais</li>
                    </ul>
                </div>
            </div>
            
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="fas fa-blog"></i>
                    </div>
                    <h3>Gerenciar Blog</h3>
                    <p>Crie e edite posts para compartilhar conhecimento sobre Direito do Consumidor</p>
                    <a href="#" class="card-btn">
                        <i class="fas fa-plus"></i>
                        Novo Post
                    </a>
                </div>
                
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Mensagens</h3>
                    <p>Visualize e responda mensagens de clientes enviadas pelo formulário de contato</p>
                    <a href="#" class="card-btn">
                        <i class="fas fa-eye"></i>
                        Ver Mensagens
                    </a>
                </div>
                
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Estatísticas</h3>
                    <p>Acompanhe o desempenho do seu site e engajamento dos visitantes</p>
                    <a href="#" class="card-btn">
                        <i class="fas fa-chart-bar"></i>
                        Ver Relatórios
                    </a>
                </div>
                
                <div class="dashboard-card">
                    <div class="icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h3>Configurações</h3>
                    <p>Atualize informações pessoais, contato e configurações do site</p>
                    <a href="#" class="card-btn">
                        <i class="fas fa-edit"></i>
                        Configurar
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        }
        
        // Fechar sidebar ao clicar em um link (mobile)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });
        
        // Redimensionar janela
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
        
        // Smooth scroll para links internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>
