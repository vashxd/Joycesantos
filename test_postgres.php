<?php
echo "<h2>🔍 Teste de Conexão PostgreSQL</h2>";

// Teste 1: Verificar driver PostgreSQL
echo "<h3>1. Drivers PostgreSQL Disponíveis:</h3>";
$drivers = PDO::getAvailableDrivers();
if (in_array('pgsql', $drivers)) {
    echo "✅ Driver PostgreSQL disponível<br>";
} else {
    echo "❌ Driver PostgreSQL NÃO disponível<br>";
    echo "📝 Drivers disponíveis: " . implode(', ', $drivers) . "<br>";
}

echo "<h3>2. Teste de Conexão:</h3>";

try {
    $pdo = new PDO(
        "pgsql:host=localhost;port=5432;dbname=joyce_santos_website", 
        "postgres", 
        "12345678",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Conexão com PostgreSQL estabelecida!<br>";
    
    // Teste 3: Verificar tabelas
    echo "<h3>3. Verificar Tabelas:</h3>";
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll();
    
    if (count($tables) > 0) {
        echo "✅ Tabelas encontradas:<br>";
        foreach ($tables as $table) {
            echo "- " . $table['table_name'] . "<br>";
        }
    } else {
        echo "⚠️ Nenhuma tabela encontrada. Execute o script de setup.<br>";
    }
    
    // Teste 4: Verificar usuário admin
    echo "<h3>4. Verificar Usuário Admin:</h3>";
    try {
        $stmt = $pdo->query("SELECT email, role FROM users WHERE role = 'admin'");
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "✅ Usuário admin encontrado: " . $admin['email'] . "<br>";
            echo "🔑 Senha padrão: 159753<br>";
        } else {
            echo "⚠️ Usuário admin não encontrado.<br>";
        }
    } catch (Exception $e) {
        echo "⚠️ Tabela users não existe. Execute o script de setup.<br>";
    }
    
    // Teste 5: Verificar posts do blog
    echo "<h3>5. Verificar Posts do Blog:</h3>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM blog_posts WHERE status = 'published'");
        $result = $stmt->fetch();
        echo "✅ Posts publicados: " . $result['total'] . "<br>";
    } catch (Exception $e) {
        echo "⚠️ Tabela blog_posts não existe. Execute o script de setup.<br>";
    }
    
    // Teste 6: Verificar configurações
    echo "<h3>6. Verificar Configurações:</h3>";
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM site_settings");
        $result = $stmt->fetch();
        echo "✅ Configurações: " . $result['total'] . "<br>";
        
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key = 'contact_email'");
        $email = $stmt->fetch();
        if ($email) {
            echo "📧 Email de contato: " . $email['setting_value'] . "<br>";
        }
    } catch (Exception $e) {
        echo "⚠️ Tabela site_settings não existe. Execute o script de setup.<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "<br>";
    echo "<br>📝 Possíveis soluções:<br>";
    echo "1. Verificar se PostgreSQL está rodando<br>";
    echo "2. Verificar credenciais (postgres/12345678)<br>";
    echo "3. Verificar se database 'joyce_santos_website' existe<br>";
    echo "4. Executar script de setup no DBeaver<br>";
}

echo "<br><h3>🎯 Próximos Passos:</h3>";
echo "1. Execute o arquivo <strong>database/setup_postgresql.sql</strong> no DBeaver<br>";
echo "2. Substitua config/database.php pelo config/database_postgresql.php<br>";
echo "3. Acesse <a href='admin/login.php'>admin/login.php</a> para fazer login<br>";
echo "4. Use: santosjoyce56@gmail.com / 159753<br>";
?>
