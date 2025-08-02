# Instalação do Site Joyce Santos

## 🚀 Guia de Instalação Rápida

### 1. Pré-requisitos
- Servidor web (Apache, Nginx, ou servidor local como XAMPP/WAMP)
- PHP 7.4 ou superior
- MySQL 5.7+ ou MariaDB 10.2+
- Extensões PHP necessárias: PDO, PDO_MySQL, mbstring

### 2. Configuração do Banco de Dados

#### Opção A: Configuração Automática
1. Acesse qualquer página do site
2. O sistema criará automaticamente as tabelas necessárias

#### Opção B: Configuração Manual
```bash
# Execute no MySQL
mysql -u root -p
source database/setup.sql
```

### 3. Configuração do Sistema

1. **Edite as configurações do banco** em `config/database.php`:
```php
$host = 'localhost';        // Seu servidor MySQL
$dbname = 'joyce_santos_website';  // Nome do banco
$username = 'root';         // Seu usuário MySQL
$password = '';             // Sua senha MySQL
```

2. **Configure permissões** (Linux/Unix):
```bash
chmod 755 assets/images/
chmod 644 *.html *.php
```

### 4. Primeiro Acesso

1. **Acesse o site**: `http://seudominio.com/`
2. **Acesse a área admin**: `http://seudominio.com/admin/`
3. **Login padrão**:
   - Email: `santosjoyce56@gmail.com`
   - Senha: `159753`

⚠️ **IMPORTANTE**: Essas são as credenciais da Joyce para acessar o painel administrativo!

### 5. Configurações Iniciais

#### No Painel Administrativo:
1. **Alterar senha do administrador**
2. **Configurar informações do site**
3. **Adicionar foto pessoal** (`assets/images/about-placeholder.jpg`)
4. **Substituir logo** (`assets/images/logo.png`)
5. **Criar primeiro post no blog**

#### Configurações de Email:
1. **Para servidor com mail() configurado**: Funciona automaticamente
2. **Para SMTP personalizado**: Configure nas configurações do site

### 6. Personalização

#### Cores e Identidade Visual:
- **Cores principais**: Já configuradas (vinho #722F37 e preto #1A1A1A)
- **Logo**: Substitua `assets/images/logo.png`
- **Favicon**: Adicione `favicon.ico` na raiz

#### Conteúdo:
1. **Sobre Mim**: Edite seção no `index.html`
2. **Áreas de Atuação**: Modifique conforme necessário
3. **Informações de Contato**: Atualize em todas as seções

### 7. Verificação da Instalação

Teste as seguintes funcionalidades:
- [ ] Site principal carrega corretamente
- [ ] Formulário de contato envia emails
- [ ] Login administrativo funciona
- [ ] Blog exibe posts (mesmo vazios)
- [ ] Mobile responsivo

### 8. Configurações de Produção

#### Segurança:
1. **Altere credenciais padrão**
2. **Configure HTTPS**
3. **Atualize regularmente**
4. **Configure backups**

#### Performance:
1. **Ative compressão GZIP**
2. **Configure cache de arquivos estáticos**
3. **Otimize imagens**
4. **Configure CDN** (opcional)

### 9. Funcionalidades Principais

#### Para a Advogada:
- ✅ **Blog gerenciável**: Criar, editar e publicar artigos
- ✅ **Mensagens de contato**: Receber e responder clientes
- ✅ **Configurações do site**: Alterar informações pessoais
- ✅ **Sistema seguro**: Login protegido e dados criptografados

#### Para os Visitantes:
- ✅ **Design profissional**: Layout moderno e responsivo
- ✅ **Fácil contato**: Formulário que envia emails diretamente
- ✅ **Blog informativo**: Artigos sobre direito do consumidor
- ✅ **Mobile friendly**: Funciona perfeitamente em celulares

### 10. Suporte e Manutenção

#### Backup Regular:
```bash
# Backup do banco de dados
mysqldump -u usuario -p joyce_santos_website > backup.sql

# Backup dos arquivos
tar -czf site_backup.tar.gz /caminho/para/site/
```

#### Logs de Erro:
- **PHP**: Verifique logs do servidor
- **MySQL**: Verifique logs de erro do banco
- **Aplicação**: Logs salvos via error_log()

#### Atualizações:
1. **Sempre faça backup antes**
2. **Teste em ambiente de desenvolvimento**
3. **Monitore logs após atualização**

### 11. Problemas Comuns

#### "Database connection failed":
- Verifique credenciais em `config/database.php`
- Confirme que MySQL está rodando
- Teste conexão manual

#### "Emails não enviados":
- Configure função mail() do PHP
- Ou configure SMTP personalizado
- Verifique logs de email

#### "Erro 500":
- Verifique logs de erro do servidor
- Confirme permissões de arquivo
- Verifique extensões PHP necessárias

### 12. Contato para Suporte

Em caso de problemas técnicos:
- Email: santosjoyce56@gmail.com
- Documente o erro com prints/logs
- Informe versões do PHP/MySQL utilizadas

---

**Parabéns! Seu site está pronto para usar! 🎉**

A Joyce Santos agora tem uma presença online profissional com todas as ferramentas necessárias para atrair e gerenciar clientes na área de Direito do Consumidor.
