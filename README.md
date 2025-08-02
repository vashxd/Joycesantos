# Site de Portfólio - Joyce Santos

Site de portfólio profissional para a advogada Joyce Santos, especialista em Direito do Consumidor.

## 🚀 Características

- **Design Responsivo**: Adaptado para desktop, tablet e mobile
- **Blog Jurídico**: Sistema completo de blog com categorias e busca
- **Área Administrativa**: Painel para gerenciar conteúdo e mensagens
- **Formulário de Contato**: Sistema de envio de emails automático
- **Newsletter**: Sistema de inscrição para newsletter
- **SEO Otimizado**: Meta tags e estrutura otimizada para buscadores
- **Segurança**: Sistema de autenticação seguro com hash de senhas

## 🛠️ Tecnologias Utilizadas

### Frontend
- HTML5 semântico
- CSS3 com variáveis customizadas
- JavaScript ES6+ vanilla
- Font Awesome para ícones
- Google Fonts (Playfair Display + Inter)

### Backend
- PHP 7.4+
- MySQL/MariaDB
- PDO para acesso ao banco de dados
- Sistema de sessões seguro

### Cores e Design
- **Cor Principal**: Vinho (#722F37)
- **Cor Secundária**: Preto (#1A1A1A)
- **Tipografia**: Playfair Display (títulos) + Inter (corpo)

## 📁 Estrutura de Arquivos

```
joyce_santos/
├── index.html                 # Página principal
├── blog.html                 # Página do blog
├── assets/
│   ├── css/
│   │   └── style.css         # Estilos principais
│   ├── js/
│   │   ├── script.js         # JavaScript principal
│   │   └── blog.js           # JavaScript do blog
│   └── images/               # Imagens do site
├── admin/                    # Área administrativa
│   ├── index.php            # Dashboard
│   ├── login.php            # Login administrativo
│   ├── logout.php           # Logout
│   └── assets/
│       └── css/
│           └── admin.css     # Estilos do admin
├── api/                      # APIs do sistema
│   ├── contact.php          # Processamento de contato
│   └── blog.php             # API do blog
├── config/
│   └── database.php         # Configuração do banco
└── database/
    └── setup.sql            # Script de criação do banco
```

## ⚙️ Instalação e Configuração

### Pré-requisitos
- Servidor web (Apache/Nginx)
- PHP 7.4 ou superior
- MySQL 5.7+ ou MariaDB 10.2+
- Extensões PHP: PDO, PDO_MySQL, mbstring

### Passo a Passo

1. **Clone ou baixe os arquivos** para seu servidor web

2. **Configure o banco de dados**:
   ```sql
   -- Execute o arquivo database/setup.sql no seu MySQL
   mysql -u root -p < database/setup.sql
   ```

3. **Configure a conexão com o banco**:
   Edite o arquivo `config/database.php` com suas credenciais:
   ```php
   $host = 'localhost';
   $dbname = 'joyce_santos_website';
   $username = 'seu_usuario';
   $password = 'sua_senha';
   ```

4. **Configure as permissões** das pastas (Linux/Unix):
   ```bash
   chmod 755 assets/images/
   chmod 644 *.html *.php
   ```

5. **Configure o email** (opcional):
   - Para envio de emails, configure um servidor SMTP
   - Ou use o mail() nativo do PHP

### Credenciais Padrão
- **Email**: santosjoyce56@gmail.com
- **Senha**: 159753

**⚠️ IMPORTANTE**: Essas são as credenciais de acesso ao painel administrativo!

## 🎨 Personalização

### Cores
Para alterar as cores do site, edite as variáveis CSS em `assets/css/style.css`:

```css
:root {
    --wine-color: #722F37;    /* Cor principal */
    --dark-wine: #5A252A;     /* Cor mais escura */
    --black-color: #1A1A1A;   /* Preto */
    --white-color: #FFFFFF;   /* Branco */
    /* ... outras variáveis */
}
```

### Logo
- Substitua o arquivo `assets/images/logo.png` pelo logo desejado
- Mantenha proporções similares para melhor resultado
- Formato recomendado: PNG com fundo transparente

### Conteúdo
- **Sobre Mim**: Edite a seção `#about` no `index.html`
- **Áreas de Atuação**: Modifique a seção `#areas`
- **Informações de Contato**: Atualize em várias seções e no rodapé

## 📧 Configuração de Email

### Método 1: mail() Nativo (Simples)
O sistema usa por padrão a função `mail()` do PHP. Certifique-se de que seu servidor está configurado para envio de emails.

### Método 2: SMTP (Recomendado)
Para maior confiabilidade, configure SMTP:

1. Instale PHPMailer via Composer (opcional)
2. Configure as credenciais SMTP no painel administrativo
3. Modifique as funções de email em `api/contact.php`

## 🔐 Segurança

### Medidas Implementadas
- Senhas hasheadas com `password_hash()`
- Proteção contra SQL Injection (PDO prepared statements)
- Proteção CSRF em formulários
- Rate limiting para contatos
- Validação de dados de entrada
- Headers de segurança

### Recomendações Adicionais
- Use HTTPS em produção
- Configure firewall adequadamente
- Mantenha PHP e dependências atualizadas
- Faça backups regulares
- Monitor logs de acesso

## 📱 Responsividade

O site é totalmente responsivo e otimizado para:
- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: até 767px

## 🔍 SEO

### Otimizações Incluídas
- Meta tags apropriadas
- Estrutura HTML semântica
- URLs amigáveis
- Schema.org markup (pode ser adicionado)
- Otimização de imagens
- Sitemap XML (pode ser gerado)

## 📊 Analytics

Para adicionar Google Analytics:
1. Acesse o painel administrativo
2. Vá em Configurações
3. Adicione seu ID do Google Analytics
4. O código será inserido automaticamente

## 🐛 Solução de Problemas

### Erro de Conexão com Banco
- Verifique as credenciais em `config/database.php`
- Certifique-se de que o MySQL está rodando
- Verifique se o banco `joyce_santos_website` existe

### Emails Não Enviados
- Verifique configuração do servidor de email
- Teste a função `mail()` do PHP
- Verifique logs de erro do servidor

### Problemas de Login
- Verifique se as tabelas foram criadas corretamente
- Certifique-se de que as sessões PHP estão funcionando
- Limpe cache do navegador

### Erro 500
- Verifique logs de erro do servidor
- Certifique-se de que todas as extensões PHP necessárias estão instaladas
- Verifique permissões de arquivo

## 📞 Suporte

Para dúvidas ou problemas:
- Email: santosjoyce56@gmail.com
- Verifique a documentação do código
- Consulte os logs de erro do servidor

## 📄 Licença

Este projeto foi desenvolvido especificamente para Joyce Santos. Todos os direitos reservados.

## 🔄 Atualizações Futuras

Funcionalidades que podem ser adicionadas:
- Sistema de comentários no blog
- Chat online
- Integração com redes sociais
- Sistema de agendamento de consultas
- Newsletter automatizada
- Relatórios de analytics
- Backup automático
- Cache de páginas
- Compressão de imagens
- CDN para assets

---

**Desenvolvido com ❤️ para Joyce Santos - Advogada Especialista em Direito do Consumidor**
