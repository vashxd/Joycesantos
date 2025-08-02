<?php
// Script para atualizar as credenciais da Joyce Santos
require_once '../config/database.php';

try {
    // Verificar se o usuário admin existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin' OR email = 'santosjoyce56@gmail.com'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        // Atualizar o usuário existente
        $newPassword = password_hash('159753', PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("
            UPDATE users 
            SET username = 'joyce', 
                email = 'santosjoyce56@gmail.com', 
                password_hash = ?,
                role = 'admin',
                status = 'active'
            WHERE id = ?
        ");
        $updateStmt->execute([$newPassword, $user['id']]);
        echo "✅ Usuário atualizado com sucesso!<br>";
        echo "📧 Email: santosjoyce56@gmail.com<br>";
        echo "🔑 Senha: 159753<br>";
    } else {
        // Criar novo usuário
        $newPassword = password_hash('159753', PASSWORD_DEFAULT);
        $insertStmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, status) 
            VALUES ('joyce', 'santosjoyce56@gmail.com', ?, 'admin', 'active')
        ");
        $insertStmt->execute([$newPassword]);
        echo "✅ Usuário criado com sucesso!<br>";
        echo "📧 Email: santosjoyce56@gmail.com<br>";
        echo "🔑 Senha: 159753<br>";
    }
    
    echo "<br><a href='login.php'>🔗 Ir para o login</a>";
    
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
