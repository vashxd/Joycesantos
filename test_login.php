<?php
echo "<h1>🔑 Teste de Login</h1>";

// Incluir configuração do banco
require_once 'config/database.php';

echo "<h2>1. Testando conexão com PostgreSQL...</h2>";
if ($pdo) {
    echo "✅ Conectado ao PostgreSQL<br>";
} else {
    echo "❌ Falha na conexão<br>";
    exit;
}

echo "<h2>2. Verificando usuário admin...</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['santosjoyce56@gmail.com']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ Usuário encontrado: " . $user['email'] . "<br>";
        echo "📧 Email: " . $user['email'] . "<br>";
        echo "👤 Username: " . $user['username'] . "<br>";
        echo "🔑 Role: " . $user['role'] . "<br>";
        echo "📅 Criado em: " . $user['created_at'] . "<br>";
    } else {
        echo "❌ Usuário admin não encontrado<br>";
    }
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

echo "<h2>3. Testando verificação de senha...</h2>";
$senha_teste = '159753';
if ($user) {
    // Verificar se a senha atual funciona
    if (password_verify($senha_teste, $user['password_hash'])) {
        echo "✅ Senha padrão funciona: $senha_teste<br>";
    } else {
        echo "❌ Senha padrão não funciona. Atualizando...<br>";
        
        // Criar nova hash da senha
        $nova_hash = password_hash($senha_teste, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$nova_hash, 'santosjoyce56@gmail.com']);
        echo "✅ Senha atualizada com nova hash<br>";
    }
}

echo "<h2>4. Links úteis:</h2>";
echo "<a href='admin/login.php'>🔗 Ir para página de login</a><br>";
echo "<a href='admin/dashboard.php'>🔗 Ir para dashboard (requer login)</a><br>";
echo "<a href='index.html'>🔗 Ir para página inicial</a><br>";

echo "<div style='background: #e7f3ff; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
echo "<strong>📝 Credenciais de Login:</strong><br>";
echo "Email: santosjoyce56@gmail.com<br>";
echo "Senha: 159753<br>";
echo "</div>";
?>
