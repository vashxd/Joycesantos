<?php
session_start();

// Credenciais da Joyce (temporário, sem banco)
$valid_users = [
    'santosjoyce56@gmail.com' => [
        'password' => '159753',
        'role' => 'admin',
        'name' => 'Joyce Santos'
    ]
];

// Função para verificar login
function isLoggedIn() {
    return isset($_SESSION['user_logged']) && $_SESSION['user_logged'] === true;
}

// Função para fazer logout
function logout() {
    session_destroy();
    header('Location: login_simple.php');
    exit();
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        if (isset($valid_users[$username]) && $valid_users[$username]['password'] === $password) {
            // Login bem-sucedido
            $_SESSION['user_logged'] = true;
            $_SESSION['user_email'] = $username;
            $_SESSION['user_name'] = $valid_users[$username]['name'];
            $_SESSION['user_role'] = $valid_users[$username]['role'];
            
            header('Location: dashboard_simple.php');
            exit();
        } else {
            $error = 'Email ou senha incorretos.';
        }
    }
}

// Logout se solicitado
if (isset($_GET['logout'])) {
    logout();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Joyce Santos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-body {
            background: linear-gradient(135deg, #722F37 0%, #1A1A1A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .login-header h2 {
            color: #722F37;
            margin: 20px 0 10px;
            font-family: 'Playfair Display', serif;
        }
        .login-header p {
            color: #666;
            margin-bottom: 30px;
        }
        .login-logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group label i {
            margin-right: 8px;
            color: #722F37;
        }
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #722F37;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: #722F37;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5a252b;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .alert i {
            margin-right: 8px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            color: #666;
            text-decoration: none;
            margin-top: 20px;
            font-size: 14px;
        }
        .back-link:hover {
            color: #722F37;
        }
        .back-link i {
            margin-right: 5px;
        }
        .credentials-hint {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
            color: #495057;
        }
        .credentials-hint strong {
            color: #722F37;
        }
    </style>
</head>
<body class="admin-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div style="width: 80px; height: 80px; background: #722F37; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fas fa-gavel" style="color: white; font-size: 24px;"></i>
                </div>
                <h2>Área Administrativa</h2>
                <p>Faça login para acessar o painel</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="credentials-hint">
                <strong>💡 Credenciais de Acesso:</strong><br>
                <strong>Email:</strong> santosjoyce56@gmail.com<br>
                <strong>Senha:</strong> 159753
            </div>
            
            <form class="login-form" method="POST" action="">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="username" 
                        name="username" 
                        required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : 'santosjoyce56@gmail.com'; ?>"
                        placeholder="santosjoyce56@gmail.com"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Senha
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        value="159753"
                        placeholder="Digite sua senha"
                    >
                </div>
                
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </button>
            </form>
            
            <div class="login-footer">
                <a href="../index.html" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao site
                </a>
            </div>
        </div>
    </div>

    <script>
        // Auto-focus no campo de email
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>
