# 🛠️ Configuração PostgreSQL no DBeaver - CORRIGIDO

## ❌ **Problema Identificado:**
O erro aconteceu porque o PostgreSQL no Windows usa collation diferente (`Portuguese_Brazil.1252` em vez de `pt_BR.UTF-8`).

## ✅ **Solução em 2 Passos:**

### **PASSO 1: Criar o Banco de Dados**

1. **Conecte-se ao PostgreSQL** no DBeaver (banco `postgres` padrão)
2. **Abra SQL Editor** (F3 ou botão SQL)
3. **Execute APENAS este comando:**

```sql
-- Execute PRIMEIRO (conectado ao banco 'postgres')
CREATE DATABASE joyce_santos_website 
WITH 
    ENCODING = 'UTF8'
    TEMPLATE = template0;
```

4. **Verifique** se o banco foi criado na lista de databases

### **PASSO 2: Conectar ao Novo Banco e Criar Tabelas**

1. **Clique com botão direito** no PostgreSQL no DBeaver
2. **Selecione "Create" > "Connection"** 
3. **Configure nova conexão:**
   - **Host**: `localhost`
   - **Port**: `5432`
   - **Database**: `joyce_santos_website` ← **IMPORTANTE: usar o banco novo**
   - **Username**: `postgres`
   - **Password**: `12345678`
4. **Teste a conexão** e clique em "Finish"
5. **Conecte-se ao novo banco** `joyce_santos_website`
6. **Abra SQL Editor** no novo banco
7. **Execute o arquivo** `setup_postgresql_part2.sql` OU copie e cole todo o conteúdo

### **Scripts Criados:**
- ✅ **`setup_postgresql_part1.sql`** - Criar banco (execute no postgres padrão)
- ✅ **`setup_postgresql_part2.sql`** - Criar tabelas (execute no joyce_santos_website)

## 🔧 **Método Alternativo - Manual no DBeaver:**

Se ainda der erro, faça manualmente:

### **1. Criar Banco:**
```sql
CREATE DATABASE joyce_santos_website WITH ENCODING = 'UTF8' TEMPLATE = template0;
```

### **2. Conectar ao novo banco e executar por partes:**

**Criar tabela de usuários:**
```sql
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'editor',
    status VARCHAR(20) DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Inserir usuário admin:**
```sql
INSERT INTO users (username, email, password_hash, role) 
VALUES ('joyce', 'santosjoyce56@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
```

**Verificar se funcionou:**
```sql
SELECT email, role FROM users WHERE role = 'admin';
```

## 🎯 **Resultado Esperado:**
- ✅ Database `joyce_santos_website` criada
- ✅ Usuário admin: `santosjoyce56@gmail.com` / `159753`
- ✅ Tabelas: users, blog_posts, contact_messages, site_settings, etc.

## 🚀 **Próximo Passo:**
Após criar o banco com sucesso:

```powershell
# Substituir arquivo de configuração
copy config\database_postgresql.php config\database.php
```

Então teste: `http://localhost:8000/test_postgres.php`

---

**💡 Dica:** Se continuar dando erro, me envie o erro específico que está aparecendo no DBeaver!
