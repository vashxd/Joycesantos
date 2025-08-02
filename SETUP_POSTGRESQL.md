# 🛠️ Configuração PostgreSQL - Joyce Santos Website

## 📋 **Passo a Passo no DBeaver**

### **1. Conectar ao PostgreSQL**
1. Abra o **DBeaver**
2. Clique em **"Nova Conexão"** (ícone de tomada com +)
3. Selecione **PostgreSQL**
4. Configure:
   - **Host**: `localhost`
   - **Port**: `5432`
   - **Database**: `postgres` (banco padrão)
   - **Username**: `postgres`
   - **Password**: `12345678`
5. Clique em **"Test Connection"** para verificar
6. Clique em **"Finish"**

### **2. Executar Script de Criação**
1. Na conexão PostgreSQL, clique com botão direito
2. Selecione **"SQL Editor" > "Open SQL script"**
3. Abra o arquivo: `database/setup_postgresql.sql`
4. **OU** copie e cole todo o conteúdo do script
5. Clique em **"Execute Script"** (Ctrl+Alt+X)
6. Aguarde a execução completa

### **3. Verificar Criação**
Após executar o script, você deve ver:
- ✅ Database `joyce_santos_website` criada
- ✅ 7 tabelas criadas
- ✅ Usuário admin inserido
- ✅ Posts de exemplo inseridos
- ✅ Configurações básicas inseridas

### **4. Verificar Dados**
Execute estas consultas para confirmar:

```sql
-- Verificar usuário admin
SELECT email, role FROM users WHERE role = 'admin';

-- Verificar posts do blog
SELECT title, status FROM blog_posts;

-- Verificar configurações
SELECT setting_key, setting_value FROM site_settings LIMIT 5;
```

## 🔧 **Configurar PHP para PostgreSQL**

### **1. Atualizar Configuração**
Substitua o arquivo `config/database.php` pelo `config/database_postgresql.php`:

```bash
# No terminal/PowerShell na pasta do projeto:
copy config\database_postgresql.php config\database.php
```

### **2. Verificar PHP PostgreSQL**
Crie um arquivo de teste:

```php
<?php
// test_postgres.php
try {
    $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=joyce_santos_website", "postgres", "12345678");
    echo "✅ Conexão PostgreSQL OK!";
    
    $stmt = $pdo->query("SELECT email FROM users WHERE role = 'admin'");
    $user = $stmt->fetch();
    echo "<br>👤 Usuário admin: " . $user['email'];
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
```

## 🎯 **Credenciais de Acesso**

### **Login do Sistema:**
- **URL**: `http://localhost:8000/admin/login.php`
- **Email**: `santosjoyce56@gmail.com`
- **Senha**: `159753`

### **Banco PostgreSQL:**
- **Host**: `localhost:5432`
- **Database**: `joyce_santos_website`
- **Username**: `postgres`
- **Password**: `12345678`

## 🚀 **Testar Sistema Completo**

### **1. Iniciar Servidor PHP**
```bash
cd "c:\Users\hscjr\Documents\projetos\joyce_santos"
php -S localhost:8000
```

### **2. Acessar Sistema**
- **Site**: http://localhost:8000/
- **Login**: http://localhost:8000/admin/login.php
- **Dashboard**: http://localhost:8000/admin/

### **3. Funcionalidades Esperadas**
- ✅ Login funcional
- ✅ Dashboard com estatísticas reais
- ✅ Blog com posts de exemplo
- ✅ Formulário de contato salvando no banco
- ✅ Sistema de mensagens
- ✅ Configurações editáveis

## 🔍 **Solução de Problemas**

### **Erro de Conexão PHP-PostgreSQL**
Se aparecer erro "could not find driver":

1. **Verificar extensão PHP**:
   ```bash
   php -m | findstr pgsql
   ```

2. **Habilitar no php.ini**:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```

3. **Reiniciar servidor PHP**

### **Tabelas não Criadas**
Se alguma tabela não foi criada:

1. Execute novamente o script completo
2. Ou execute linha por linha no DBeaver
3. Verifique permissões do usuário postgres

### **Erro de Charset**
Se houver problemas com acentos:

1. Verifique encoding da database: `UTF8`
2. Configure PHP para UTF-8
3. Use `header('Content-Type: text/html; charset=UTF-8');`

## ✅ **Verificação Final**

Execute no DBeaver para confirmar tudo:

```sql
-- Estatísticas gerais
SELECT 
    'Usuários' as tabela, 
    count(*) as registros 
FROM users
UNION ALL
SELECT 
    'Posts Blog' as tabela, 
    count(*) as registros 
FROM blog_posts
UNION ALL
SELECT 
    'Configurações' as tabela, 
    count(*) as registros 
FROM site_settings;

-- Credenciais de login
SELECT 
    'CREDENCIAL LOGIN:' as info,
    email as usuario,
    '159753' as senha,
    role as funcao
FROM users 
WHERE role = 'admin';
```

**Esperado:**
- 1 usuário admin
- 3 posts no blog
- ~18 configurações
- Email: santosjoyce56@gmail.com

---

🎉 **Após seguir esses passos, o sistema estará completamente funcional com PostgreSQL!**
