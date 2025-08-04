# Deploy no Railway.app - Joyce Santos Website

## 🚀 Passo a Passo para Deploy

### 1. Preparação do Repositório GitHub
```bash
# Se ainda não tem o projeto no GitHub, criar:
git init
git add .
git commit -m "Initial commit - Joyce Santos website"
git branch -M main
git remote add origin https://github.com/vashxd/Joycesantos.git
git push -u origin main
```

### 2. Deploy no Railway

1. **Acesse https://railway.app**
2. **Clique em "New Project"**
3. **Selecione "Deploy from GitHub repo"**
4. **Escolha o repositório: vashxd/Joycesantos**
5. **Railway detectará automaticamente que é um projeto PHP**

### 3. Adicionar PostgreSQL Database

1. **No projeto Railway, clique em "Add Service"**
2. **Selecione "Database" → "PostgreSQL"**
3. **Aguarde a criação do banco (1-2 minutos)**

### 4. Configurar Variáveis de Ambiente (Automático)

O Railway conecta automaticamente o banco. As variáveis criadas serão:
- `DATABASE_URL`
- `PGHOST`
- `PGPORT` 
- `PGDATABASE`
- `PGUSER`
- `PGPASSWORD`

### 5. Inicializar o Banco de Dados

Após o deploy, acesse:
```
https://[seu-app].railway.app/?setup=database
```

### 6. Verificar se está funcionando

- **Site principal**: https://[seu-app].railway.app
- **Admin**: https://[seu-app].railway.app/admin/login.php
- **API Blog**: https://[seu-app].railway.app/api/blog.php?limit=3

## 🔑 Credenciais Padrão

**Admin Login:**
- Email: `admin@joycesantos.com`
- Senha: `159753`

## 📁 Estrutura dos Arquivos para Railway

```
/
├── index.php              # Roteador principal
├── index.html             # Página principal
├── railway.json           # Configuração Railway
├── nixpacks.toml          # Configuração build
├── setup_database_railway.php  # Setup do banco
├── config/
│   └── database.php       # Configuração DB (com env vars)
├── api/
│   └── blog.php          # API do blog
├── admin/                # Painel administrativo
├── assets/               # CSS, JS, imagens
└── database/             # Scripts SQL
```

## 🔧 Troubleshooting

### Se o banco não inicializar automaticamente:
1. Vá no Railway Dashboard
2. Clique no seu projeto
3. Clique em "Deployments"
4. Na última deployment, clique nos "3 pontos" → "View Logs"
5. Se houver erro, acesse: `https://[seu-app].railway.app/?setup=database`

### Se houver erro de conexão:
1. Verifique se o PostgreSQL service está "running" (verde)
2. Verifique se as variáveis de ambiente estão conectadas
3. No service do app, vá em "Variables" e confirme que as variáveis do banco estão lá

## 📊 Monitoramento

- **Logs**: Railway Dashboard → Seu Projeto → Deployments → View Logs
- **Métricas**: Railway Dashboard → Seu Projeto → Metrics
- **Banco**: Railway Dashboard → PostgreSQL → Connect

## 🎯 URLs Importantes

Substitua `[seu-app]` pelo nome gerado pelo Railway:

- **Site**: https://[seu-app].railway.app
- **Blog API**: https://[seu-app].railway.app/api/blog
- **Admin**: https://[seu-app].railway.app/admin/login
- **Setup DB**: https://[seu-app].railway.app/?setup=database

---

**Pronto! 🎉 Seu site está no ar com Railway + PostgreSQL!**
