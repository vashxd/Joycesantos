<?php
echo "🚀 Iniciando setup do banco de dados no Railway...\n";

require_once 'config/database.php';

try {
    // Verifica se as tabelas já existem
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users'");
    $tables = $stmt->fetchAll();
    
    if (count($tables) > 0) {
        echo "✅ Tabelas já existem. Verificando dados...\n";
        
        // Verifica se existem usuários
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $userCount = $stmt->fetch()['count'];
        
        // Verifica se existem posts
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM blog_posts");
        $postCount = $stmt->fetch()['count'];
        
        echo "👥 Usuários no banco: {$userCount}\n";
        echo "📝 Posts no blog: {$postCount}\n";
        
        if ($userCount > 0 && $postCount > 0) {
            echo "✅ Banco já está configurado e com dados!\n";
            exit(0);
        }
    }
    
    echo "🔧 Executando setup do banco de dados...\n";
    
    // Lê e executa o arquivo SQL
    $sql = file_get_contents('database/setup_postgresql_part2.sql');
    
    if ($sql === false) {
        throw new Exception("Não foi possível ler o arquivo SQL");
    }
    
    // Remove comentários e executa
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && strpos($stmt, '--') !== 0;
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "⚠️ Erro na statement: " . substr($statement, 0, 50) . "...\n";
                    echo "Erro: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    // Verifica o resultado
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM blog_posts WHERE status = 'published'");
    $postCount = $stmt->fetch()['count'];
    
    echo "✅ Setup concluído!\n";
    echo "👥 Usuários criados: {$userCount}\n";
    echo "📝 Posts publicados: {$postCount}\n";
    
    // Mostra credenciais de login
    $stmt = $pdo->query("SELECT email FROM users WHERE role = 'admin' LIMIT 1");
    $admin = $stmt->fetch();
    if ($admin) {
        echo "\n🔑 CREDENCIAIS DE LOGIN:\n";
        echo "📧 Email: {$admin['email']}\n";
        echo "🔐 Senha: 159753\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro no setup: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Banco de dados configurado com sucesso no Railway!\n";
?>
