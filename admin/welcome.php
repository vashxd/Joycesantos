<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuração Inicial - Joyce Santos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .setup-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--wine-color), var(--dark-wine));
            padding: 2rem;
        }
        .setup-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        .setup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .setup-header h1 {
            color: var(--wine-color);
            margin-bottom: 1rem;
        }
        .setup-step {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--wine-color);
        }
        .setup-step h3 {
            color: var(--wine-color);
            margin-bottom: 1rem;
        }
        .credentials-box {
            background: #e8f5e8;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            font-family: monospace;
            font-size: 1.1rem;
        }
        .copy-btn {
            background: var(--wine-color);
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .status {
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-card">
            <div class="setup-header">
                <h1>🎉 Site Configurado com Sucesso!</h1>
                <p>Bem-vinda ao seu novo site, Joyce!</p>
            </div>

            <?php
            require_once '../config/database.php';
            
            $status = '';
            $errors = [];
            
            // Verificar conexão com banco
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $userCount = $stmt->fetchColumn();
                
                if ($userCount > 0) {
                    $status = 'success';
                    $message = 'Banco de dados configurado e usuário criado!';
                } else {
                    $status = 'error';
                    $message = 'Usuário não encontrado no banco de dados.';
                }
            } catch (Exception $e) {
                $status = 'error';
                $message = 'Erro na conexão com o banco: ' . $e->getMessage();
            }
            ?>

            <?php if ($status): ?>
                <div class="status <?php echo $status; ?>">
                    <strong><?php echo $message; ?></strong>
                </div>
            <?php endif; ?>

            <div class="setup-step">
                <h3>📧 Suas Credenciais de Acesso</h3>
                <p>Use estas informações para acessar o painel administrativo:</p>
                
                <div class="credentials-box">
                    <div class="credential-item">
                        <span><strong>Email:</strong> santosjoyce56@gmail.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('santosjoyce56@gmail.com')">Copiar</button>
                    </div>
                    <div class="credential-item">
                        <span><strong>Senha:</strong> 159753</span>
                        <button class="copy-btn" onclick="copyToClipboard('159753')">Copiar</button>
                    </div>
                </div>
            </div>

            <div class="setup-step">
                <h3>🚀 Próximos Passos</h3>
                <ol>
                    <li><strong>Faça seu primeiro login</strong> no painel administrativo</li>
                    <li><strong>Adicione sua foto</strong> na seção "Sobre Mim"</li>
                    <li><strong>Substitua o logo</strong> pela sua identidade visual</li>
                    <li><strong>Crie seu primeiro post</strong> no blog</li>
                    <li><strong>Configure suas redes sociais</strong> no rodapé</li>
                </ol>
            </div>

            <div class="setup-step">
                <h3>📱 Links Importantes</h3>
                <div style="display: grid; gap: 1rem; margin-top: 1rem;">
                    <a href="../index.html" class="btn btn-outline" target="_blank">
                        🌐 Ver Site Principal
                    </a>
                    <a href="login.php" class="btn btn-primary">
                        🔐 Acessar Painel Administrativo
                    </a>
                    <a href="../blog.html" class="btn btn-outline" target="_blank">
                        📖 Ver Blog
                    </a>
                </div>
            </div>

            <div class="setup-step">
                <h3>💡 Dicas de Uso</h3>
                <ul>
                    <li><strong>Blog:</strong> Publique regularmente artigos sobre Direito do Consumidor</li>
                    <li><strong>SEO:</strong> Use palavras-chave relevantes nos títulos dos posts</li>
                    <li><strong>Imagens:</strong> Adicione imagens atrativas aos seus artigos</li>
                    <li><strong>Contato:</strong> Responda rapidamente às mensagens recebidas</li>
                </ul>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <p style="color: #666;">
                    <strong>Suporte:</strong> santosjoyce56@gmail.com<br>
                    <small>Em caso de dúvidas, não hesite em entrar em contato!</small>
                </p>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copiado para a área de transferência!');
            }, function(err) {
                console.error('Erro ao copiar: ', err);
            });
        }
    </script>
</body>
</html>
