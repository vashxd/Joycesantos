<?php
echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'><title>Setup Completo do Banco</title></head><body>";
echo "<h1>🚀 Setup Completo do Banco PostgreSQL</h1>";

// Configurações do banco
$host = 'localhost';
$port = '5432';
$admin_user = 'postgres';
$admin_password = '12345678';
$database_name = 'joyce_santos_website';

echo "<h2>1. Criando o banco de dados...</h2>";

try {
    // Conectar ao PostgreSQL como admin para criar o banco
    $admin_pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=postgres", 
        $admin_user, 
        $admin_password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Conectado ao PostgreSQL como administrador<br>";
    
    // Verificar se o banco já existe
    $stmt = $admin_pdo->prepare("SELECT 1 FROM pg_database WHERE datname = ?");
    $stmt->execute([$database_name]);
    
    if ($stmt->fetchColumn()) {
        echo "ℹ️ Banco '$database_name' já existe<br>";
    } else {
        // Criar o banco
        $admin_pdo->exec("CREATE DATABASE $database_name WITH ENCODING = 'UTF8' TEMPLATE = template0");
        echo "✅ Banco '$database_name' criado com sucesso!<br>";
    }
    
    $admin_pdo = null; // Fechar conexão admin
    
} catch (PDOException $e) {
    echo "❌ Erro ao criar banco: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>2. Conectando ao banco criado...</h2>";

try {
    // Conectar ao banco específico
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$database_name", 
        $admin_user, 
        $admin_password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Conectado ao banco '$database_name'<br>";
    
} catch (PDOException $e) {
    echo "❌ Erro ao conectar ao banco: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h2>3. Criando extensões...</h2>";

try {
    $pdo->exec('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
    echo "✅ Extensão uuid-ossp criada<br>";
} catch (PDOException $e) {
    echo "⚠️ Aviso extensão: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Criando tabelas...</h2>";

// Array com todas as queries SQL
$sql_queries = [
    // Tabela users
    "CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'editor' CHECK (role IN ('admin', 'editor')),
        status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
        last_login TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Índices users
    "CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)",
    "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
    "CREATE INDEX IF NOT EXISTS idx_users_status ON users(status)",
    
    // Tabela blog_posts
    "CREATE TABLE IF NOT EXISTS blog_posts (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        excerpt TEXT,
        content TEXT NOT NULL,
        image VARCHAR(255),
        category VARCHAR(100),
        tags TEXT,
        status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
        featured BOOLEAN DEFAULT FALSE,
        views INTEGER DEFAULT 0,
        author_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Índices blog_posts
    "CREATE INDEX IF NOT EXISTS idx_blog_posts_status ON blog_posts(status)",
    "CREATE INDEX IF NOT EXISTS idx_blog_posts_category ON blog_posts(category)",
    "CREATE INDEX IF NOT EXISTS idx_blog_posts_featured ON blog_posts(featured)",
    "CREATE INDEX IF NOT EXISTS idx_blog_posts_created_at ON blog_posts(created_at)",
    "CREATE INDEX IF NOT EXISTS idx_blog_posts_slug ON blog_posts(slug)",
    
    // Tabela contact_messages
    "CREATE TABLE IF NOT EXISTS contact_messages (
        id SERIAL PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied', 'archived')),
        ip_address INET,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Índices contact_messages
    "CREATE INDEX IF NOT EXISTS idx_contact_messages_status ON contact_messages(status)",
    "CREATE INDEX IF NOT EXISTS idx_contact_messages_email ON contact_messages(email)",
    "CREATE INDEX IF NOT EXISTS idx_contact_messages_created_at ON contact_messages(created_at)",
    
    // Tabela site_settings
    "CREATE TABLE IF NOT EXISTS site_settings (
        id SERIAL PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_type VARCHAR(20) DEFAULT 'text' CHECK (setting_type IN ('text', 'textarea', 'boolean', 'number', 'email', 'url')),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Tabela user_sessions
    "CREATE TABLE IF NOT EXISTS user_sessions (
        session_id VARCHAR(128) PRIMARY KEY,
        user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
        ip_address INET,
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL
    )",
    
    // Índices user_sessions
    "CREATE INDEX IF NOT EXISTS idx_user_sessions_user_id ON user_sessions(user_id)",
    "CREATE INDEX IF NOT EXISTS idx_user_sessions_expires_at ON user_sessions(expires_at)",
    
    // Tabela newsletter_subscribers
    "CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id SERIAL PRIMARY KEY,
        email VARCHAR(100) NOT NULL UNIQUE,
        status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'unsubscribed')),
        confirmed BOOLEAN DEFAULT FALSE,
        confirmation_token VARCHAR(255),
        ip_address INET,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Índices newsletter_subscribers
    "CREATE INDEX IF NOT EXISTS idx_newsletter_subscribers_email ON newsletter_subscribers(email)",
    "CREATE INDEX IF NOT EXISTS idx_newsletter_subscribers_status ON newsletter_subscribers(status)",
    
    // Tabela post_views
    "CREATE TABLE IF NOT EXISTS post_views (
        id SERIAL PRIMARY KEY,
        post_id INTEGER NOT NULL REFERENCES blog_posts(id) ON DELETE CASCADE,
        ip_address INET,
        user_agent TEXT,
        referer VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Índices post_views
    "CREATE INDEX IF NOT EXISTS idx_post_views_post_id ON post_views(post_id)",
    "CREATE INDEX IF NOT EXISTS idx_post_views_created_at ON post_views(created_at)"
];

// Executar cada query
foreach ($sql_queries as $index => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Query " . ($index + 1) . " executada<br>";
    } catch (PDOException $e) {
        echo "❌ Erro na query " . ($index + 1) . ": " . $e->getMessage() . "<br>";
    }
}

echo "<h2>5. Criando função e triggers...</h2>";

try {
    // Função para atualizar updated_at
    $pdo->exec("
        CREATE OR REPLACE FUNCTION update_updated_at_column()
        RETURNS TRIGGER AS \$\$
        BEGIN
            NEW.updated_at = CURRENT_TIMESTAMP;
            RETURN NEW;
        END;
        \$\$ language 'plpgsql'
    ");
    echo "✅ Função update_updated_at_column criada<br>";
    
    // Triggers
    $triggers = [
        "CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()",
        "CREATE TRIGGER update_blog_posts_updated_at BEFORE UPDATE ON blog_posts FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()",
        "CREATE TRIGGER update_contact_messages_updated_at BEFORE UPDATE ON contact_messages FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()",
        "CREATE TRIGGER update_site_settings_updated_at BEFORE UPDATE ON site_settings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()",
        "CREATE TRIGGER update_newsletter_subscribers_updated_at BEFORE UPDATE ON newsletter_subscribers FOR EACH ROW EXECUTE FUNCTION update_updated_at_column()"
    ];
    
    foreach ($triggers as $trigger) {
        try {
            $pdo->exec($trigger);
        } catch (PDOException $e) {
            // Ignorar erro se trigger já existe
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "⚠️ " . $e->getMessage() . "<br>";
            }
        }
    }
    echo "✅ Triggers criados<br>";
    
} catch (PDOException $e) {
    echo "❌ Erro criando função/triggers: " . $e->getMessage() . "<br>";
}

echo "<h2>6. Inserindo dados iniciais...</h2>";

try {
    // Inserir usuário admin
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, role) 
        VALUES (?, ?, ?, ?)
        ON CONFLICT (email) DO NOTHING
    ");
    $stmt->execute(['joyce', 'santosjoyce56@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin']);
    echo "✅ Usuário admin criado<br>";
    
    // Inserir configurações do site
    $settings = [
        ['site_name', 'Joyce Santos - Advogada', 'text', 'Nome do site'],
        ['site_description', 'Advogada Especialista em Direito do Consumidor', 'textarea', 'Descrição do site'],
        ['contact_email', 'santosjoyce56@gmail.com', 'email', 'Email de contato principal'],
        ['contact_phone', '(11) 99999-9999', 'text', 'Telefone de contato'],
        ['address', 'São Paulo, SP', 'text', 'Endereço'],
        ['oab_number', 'OAB/SP 123.456', 'text', 'Número da OAB'],
        ['blog_posts_per_page', '10', 'number', 'Posts por página no blog'],
        ['maintenance_mode', 'false', 'boolean', 'Modo de manutenção']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO site_settings (setting_key, setting_value, setting_type, description) 
        VALUES (?, ?, ?, ?)
        ON CONFLICT (setting_key) DO NOTHING
    ");
    
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }
    echo "✅ Configurações do site inseridas<br>";
    
    // Inserir posts de exemplo
    $stmt = $pdo->prepare("
        INSERT INTO blog_posts (title, slug, excerpt, content, category, tags, status, featured, author_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON CONFLICT (slug) DO NOTHING
    ");
    
    $posts = [
        [
            'Seus Direitos nas Compras Online: O que Todo Consumidor Precisa Saber',
            'direitos-compras-online-consumidor',
            'Com o crescimento do e-commerce, é fundamental conhecer seus direitos ao fazer compras pela internet.',
            '<h2>Direitos do Consumidor Online</h2><p>Conheça seus direitos ao fazer compras pela internet...</p>',
            'dicas',
            'direito do consumidor,compras online,e-commerce,CDC',
            'published',
            true,
            1
        ],
        [
            'Problemas com Operadoras de Telefone: Como Resolver',
            'problemas-operadoras-telefone-resolver',
            'Falta de sinal, cobrança indevida, cancelamento não autorizado... Saiba como resolver.',
            '<h2>Problemas Mais Comuns</h2><p>As operadoras de telefonia são uma das maiores fontes de reclamações...</p>',
            'artigos',
            'telecomunicações,operadoras,direitos,Anatel',
            'published',
            false,
            1
        ]
    ];
    
    foreach ($posts as $post) {
        $stmt->execute($post);
    }
    echo "✅ Posts de exemplo inseridos<br>";
    
} catch (PDOException $e) {
    echo "❌ Erro inserindo dados: " . $e->getMessage() . "<br>";
}

echo "<h2>7. Verificando resultado...</h2>";

try {
    // Verificar tabelas criadas
    $stmt = $pdo->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll();
    
    echo "<strong>Tabelas criadas:</strong><br>";
    foreach ($tables as $table) {
        echo "- " . $table['table_name'] . "<br>";
    }
    
    // Contar registros
    $counts = [
        'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'blog_posts' => $pdo->query("SELECT COUNT(*) FROM blog_posts")->fetchColumn(),
        'site_settings' => $pdo->query("SELECT COUNT(*) FROM site_settings")->fetchColumn()
    ];
    
    echo "<br><strong>Registros inseridos:</strong><br>";
    foreach ($counts as $table => $count) {
        echo "- $table: $count registros<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro na verificação: " . $e->getMessage() . "<br>";
}

echo "<h2>🎉 Setup Concluído!</h2>";
echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<strong>✅ Banco de dados criado e configurado com sucesso!</strong><br><br>";
echo "<strong>Credenciais de acesso:</strong><br>";
echo "Email: santosjoyce56@gmail.com<br>";
echo "Senha: 159753<br><br>";
echo "<strong>Próximos passos:</strong><br>";
echo "1. Acesse <a href='admin/login.php'>admin/login.php</a> para fazer login<br>";
echo "2. Verifique se o arquivo config/database_postgresql.php está configurado<br>";
echo "3. Teste o site acessando index.html<br>";
echo "</div>";

echo "</body></html>";
?>
