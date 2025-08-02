-- PostgreSQL setup script for Joyce Santos website
-- Run this script in DBeaver connected to PostgreSQL

-- Create database (Windows compatible)
CREATE DATABASE joyce_santos_website 
WITH 
    ENCODING = 'UTF8'
    TEMPLATE = template0;

-- Connect to the new database before running the rest
\c joyce_santos_website;

-- Create extensions if needed
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'editor' CHECK (role IN ('admin', 'editor')),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);

-- Blog posts table
CREATE TABLE IF NOT EXISTS blog_posts (
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
);

CREATE INDEX IF NOT EXISTS idx_blog_posts_status ON blog_posts(status);
CREATE INDEX IF NOT EXISTS idx_blog_posts_category ON blog_posts(category);
CREATE INDEX IF NOT EXISTS idx_blog_posts_featured ON blog_posts(featured);
CREATE INDEX IF NOT EXISTS idx_blog_posts_created_at ON blog_posts(created_at);
CREATE INDEX IF NOT EXISTS idx_blog_posts_slug ON blog_posts(slug);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
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
);

CREATE INDEX IF NOT EXISTS idx_contact_messages_status ON contact_messages(status);
CREATE INDEX IF NOT EXISTS idx_contact_messages_email ON contact_messages(email);
CREATE INDEX IF NOT EXISTS idx_contact_messages_created_at ON contact_messages(created_at);

-- Site settings table
CREATE TABLE IF NOT EXISTS site_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'text' CHECK (setting_type IN ('text', 'textarea', 'boolean', 'number', 'email', 'url')),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    session_id VARCHAR(128) PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    ip_address INET,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_user_sessions_user_id ON user_sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_user_sessions_expires_at ON user_sessions(expires_at);

-- Newsletter subscribers table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id SERIAL PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'unsubscribed')),
    confirmed BOOLEAN DEFAULT FALSE,
    confirmation_token VARCHAR(255),
    ip_address INET,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_newsletter_subscribers_email ON newsletter_subscribers(email);
CREATE INDEX IF NOT EXISTS idx_newsletter_subscribers_status ON newsletter_subscribers(status);

-- Blog post views table (for analytics)
CREATE TABLE IF NOT EXISTS post_views (
    id SERIAL PRIMARY KEY,
    post_id INTEGER NOT NULL REFERENCES blog_posts(id) ON DELETE CASCADE,
    ip_address INET,
    user_agent TEXT,
    referer VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_post_views_post_id ON post_views(post_id);
CREATE INDEX IF NOT EXISTS idx_post_views_created_at ON post_views(created_at);

-- Function to update updated_at column
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_blog_posts_updated_at BEFORE UPDATE ON blog_posts FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_contact_messages_updated_at BEFORE UPDATE ON contact_messages FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_site_settings_updated_at BEFORE UPDATE ON site_settings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_newsletter_subscribers_updated_at BEFORE UPDATE ON newsletter_subscribers FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Insert default admin user
-- Password: 159753 (hashed with bcrypt)
INSERT INTO users (username, email, password_hash, role) 
VALUES ('joyce', 'santosjoyce56@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON CONFLICT (email) DO NOTHING;

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Joyce Santos - Advogada', 'text', 'Nome do site'),
('site_description', 'Advogada Especialista em Direito do Consumidor', 'textarea', 'Descrição do site'),
('contact_email', 'santosjoyce56@gmail.com', 'email', 'Email de contato principal'),
('contact_phone', '(11) 99999-9999', 'text', 'Telefone de contato'),
('address', 'São Paulo, SP', 'text', 'Endereço'),
('oab_number', 'OAB/SP 123.456', 'text', 'Número da OAB'),
('social_linkedin', '', 'url', 'LinkedIn URL'),
('social_instagram', '', 'url', 'Instagram URL'),
('social_facebook', '', 'url', 'Facebook URL'),
('google_analytics', '', 'text', 'Google Analytics ID'),
('blog_posts_per_page', '10', 'number', 'Posts por página no blog'),
('maintenance_mode', 'false', 'boolean', 'Modo de manutenção'),
('allow_comments', 'false', 'boolean', 'Permitir comentários no blog'),
('smtp_host', '', 'text', 'Servidor SMTP'),
('smtp_port', '587', 'number', 'Porta SMTP'),
('smtp_username', '', 'text', 'Usuário SMTP'),
('smtp_password', '', 'text', 'Senha SMTP'),
('smtp_encryption', 'tls', 'text', 'Criptografia SMTP')
ON CONFLICT (setting_key) DO NOTHING;

-- Insert sample blog posts
INSERT INTO blog_posts (title, slug, excerpt, content, category, tags, status, featured, author_id) VALUES
(
    'Seus Direitos nas Compras Online: O que Todo Consumidor Precisa Saber',
    'direitos-compras-online-consumidor',
    'Com o crescimento do e-commerce, é fundamental conhecer seus direitos ao fazer compras pela internet. Saiba como se proteger e o que fazer em caso de problemas.',
    '<h2>Introdução</h2><p>O comércio eletrônico revolucionou a forma como fazemos compras, oferecendo praticidade e variedade. No entanto, com essa facilidade, também surgiram novos desafios para os consumidores. É essencial conhecer seus direitos para fazer compras seguras e saber como proceder em caso de problemas.</p><h2>Principais Direitos do Consumidor Online</h2><h3>1. Direito de Arrependimento</h3><p>Uma das principais proteções do consumidor em compras online é o direito de arrependimento, previsto no artigo 49 do Código de Defesa do Consumidor (CDC). Você tem até 7 dias corridos para desistir da compra, contados a partir do recebimento do produto.</p><h3>2. Informações Claras</h3><p>A loja virtual deve fornecer informações completas sobre:</p><ul><li>Características do produto</li><li>Preço total (incluindo frete e taxas)</li><li>Prazo de entrega</li><li>Dados da empresa (CNPJ, endereço, telefone)</li></ul><h3>3. Segurança nas Transações</h3><p>O site deve garantir a segurança dos seus dados pessoais e financeiros, utilizando certificados de segurança e sistemas de proteção adequados.</p><h2>Como Proceder em Caso de Problemas</h2><h3>Produto não entregue</h3><p>Se o produto não chegou no prazo estipulado:</p><ol><li>Entre em contato com a loja</li><li>Guarde todos os comprovantes</li><li>Procure o Procon ou um advogado se necessário</li></ol><h3>Produto com defeito</h3><p>Em caso de vício do produto, você tem direito a:</p><ul><li>Troca por produto sem defeito</li><li>Devolução do dinheiro</li><li>Abatimento proporcional do preço</li></ul><h2>Conclusão</h2><p>Conhecer seus direitos é fundamental para fazer compras online com segurança. Em caso de dúvidas ou problemas, não hesite em buscar orientação jurídica especializada.</p>',
    'dicas',
    'direito do consumidor,compras online,e-commerce,CDC',
    'published',
    TRUE,
    1
),
(
    'Problemas com Operadoras de Telefone: Como Resolver',
    'problemas-operadoras-telefone-resolver',
    'Falta de sinal, cobrança indevida, cancelamento não autorizado... Saiba como resolver os principais problemas com operadoras de telefonia.',
    '<h2>Problemas Mais Comuns</h2><p>As operadoras de telefonia são uma das maiores fontes de reclamações no Procon. Os problemas mais frequentes incluem:</p><ul><li>Cobranças indevidas</li><li>Falta de sinal</li><li>Cancelamento não autorizado de serviços</li><li>Dificuldade para cancelar contratos</li><li>Propaganda enganosa</li></ul><h2>Seus Direitos</h2><p>Como consumidor de serviços de telecomunicações, você tem direitos específicos que devem ser respeitados pelas operadoras.</p><h3>Qualidade do Serviço</h3><p>A operadora deve fornecer o serviço conforme contratado, com a qualidade prometida. Se isso não acontecer, você pode exigir:</p><ul><li>Reparo do serviço</li><li>Desconto na fatura</li><li>Cancelamento sem multa</li></ul><h3>Informações Claras</h3><p>Todas as condições do contrato devem ser explicadas de forma clara, incluindo:</p><ul><li>Valores e formas de pagamento</li><li>Prazo de fidelidade</li><li>Condições para cancelamento</li><li>Multas aplicáveis</li></ul><h2>Como Resolver Problemas</h2><h3>1. Contate a Operadora</h3><p>Sempre tente resolver primeiro diretamente com a operadora. Guarde o número do protocolo de atendimento.</p><h3>2. Anatel</h3><p>Se não conseguir resolver com a operadora, registre uma reclamação na Anatel pelo site ou telefone 1331.</p><h3>3. Procon</h3><p>O Procon também pode mediar conflitos entre consumidores e operadoras.</p><h3>4. Justiça</h3><p>Em casos mais complexos, pode ser necessário buscar apoio jurídico para uma ação judicial.</p><h2>Dicas Importantes</h2><ul><li>Sempre guarde todos os comprovantes e contratos</li><li>Documente todas as conversas e protocolos</li><li>Conheça seus direitos antes de assinar qualquer contrato</li><li>Leia sempre a letra miúda</li></ul>',
    'artigos',
    'telecomunicações,operadoras,direitos,Anatel',
    'published',
    FALSE,
    1
),
(
    'Direito de Arrependimento: 7 Dias para Mudar de Ideia',
    'direito-arrependimento-7-dias',
    'O direito de arrependimento é uma importante proteção do consumidor. Entenda como funciona e quando pode ser aplicado.',
    '<h2>O que é o Direito de Arrependimento?</h2><p>O direito de arrependimento está previsto no artigo 49 do Código de Defesa do Consumidor e permite que o consumidor desista da compra em até 7 dias corridos, sem precisar justificar o motivo.</p><h2>Quando se Aplica?</h2><p>Este direito vale para compras realizadas:</p><ul><li>Fora do estabelecimento comercial (pela internet, telefone, etc.)</li><li>Em domicílio</li><li>Por meio de vendedores</li><li>Em eventos ou feiras</li></ul><h2>Como Exercer este Direito</h2><h3>Prazo</h3><p>Você tem 7 dias corridos a partir do recebimento do produto ou da assinatura do contrato para desistir.</p><h3>Procedimento</h3><ol><li>Comunique a empresa sobre sua decisão</li><li>Devolva o produto nas mesmas condições</li><li>Guarde todos os comprovantes</li></ol><h3>Devolução do Dinheiro</h3><p>A empresa deve devolver todos os valores pagos, incluindo frete, de forma imediata.</p><h2>Casos Especiais</h2><h3>Produtos Personalizados</h3><p>Produtos feitos sob medida ou personalizados podem ter regras diferentes.</p><h3>Serviços Digitais</h3><p>Para serviços digitais, o prazo pode ser diferente se você já começou a utilizar o serviço.</p><h2>Importante Saber</h2><ul><li>O produto deve ser devolvido na embalagem original</li><li>Você não pode usar excessivamente o produto</li><li>A empresa não pode cobrar taxa de restocking</li><li>O frete de devolução pode ser por conta do consumidor</li></ul>',
    'dicas',
    'arrependimento,CDC,direitos,devolução',
    'published',
    FALSE,
    1
)
ON CONFLICT (slug) DO NOTHING;

-- Verify data
SELECT 'Usuários criados:' as info, count(*) as total FROM users;
SELECT 'Posts do blog:' as info, count(*) as total FROM blog_posts;
SELECT 'Configurações:' as info, count(*) as total FROM site_settings;

-- Show admin user
SELECT 'CREDENCIAIS DE LOGIN:' as info;
SELECT email, 'Senha: 159753' as password FROM users WHERE role = 'admin';
