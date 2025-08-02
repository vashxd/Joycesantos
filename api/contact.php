<?php
session_start();
require_once '../config/database.php';

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    // Get and validate form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Nome deve ter pelo menos 2 caracteres';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    if (empty($subject)) {
        $errors[] = 'Assunto é obrigatório';
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = 'Mensagem deve ter pelo menos 10 caracteres';
    }
    
    // Check for spam (simple rate limiting)
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages WHERE ip_address = ? AND created_at > NOW() - INTERVAL 1 HOUR");
    $stmt->execute([$ip]);
    
    if ($stmt->fetchColumn() > 5) {
        $errors[] = 'Muitas mensagens enviadas. Tente novamente em 1 hora.';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit();
    }
    
    // Save to database
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$name, $email, $phone, $subject, $message, $ip]);
    
    // Send email notification
    $emailSent = sendContactNotification($name, $email, $phone, $subject, $message);
    
    // Send auto-reply to user
    sendAutoReply($email, $name);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Mensagem enviada com sucesso!',
        'id' => $pdo->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log("Contact form error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}

function sendContactNotification($name, $email, $phone, $subject, $message) {
    $to = 'santosjoyce56@gmail.com';
    $emailSubject = "Nova mensagem do site - $subject";
    
    $emailBody = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #722F37; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #722F37; }
            .value { margin-top: 5px; }
            .message-box { background: white; padding: 15px; border-left: 4px solid #722F37; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nova Mensagem do Site</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Nome:</div>
                    <div class='value'>$name</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>$email</div>
                </div>
                <div class='field'>
                    <div class='label'>Telefone:</div>
                    <div class='value'>" . ($phone ?: 'Não informado') . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Assunto:</div>
                    <div class='value'>$subject</div>
                </div>
                <div class='field'>
                    <div class='label'>Mensagem:</div>
                    <div class='message-box'>$message</div>
                </div>
                <hr>
                <p><small>Enviado em: " . date('d/m/Y H:i:s') . "</small></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Site Joyce Santos <noreply@joycesantos.com.br>',
        'Reply-To: ' . $email
    ];
    
    return mail($to, $emailSubject, $emailBody, implode("\r\n", $headers));
}

function sendAutoReply($email, $name) {
    $subject = "Obrigada pelo seu contato - Joyce Santos";
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #722F37; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
            .signature { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Joyce Santos - Advogada</h2>
            </div>
            <div class='content'>
                <p>Olá <strong>$name</strong>,</p>
                
                <p>Obrigada por entrar em contato comigo!</p>
                
                <p>Recebi sua mensagem e retornarei o contato em até 24 horas úteis. Enquanto isso, fique à vontade para conhecer mais sobre meu trabalho em meu site.</p>
                
                <p>Como advogada especialista em Direito do Consumidor, estou aqui para ajudar você a resolver seus problemas e garantir que seus direitos sejam respeitados.</p>
                
                <div class='signature'>
                    <p><strong>Joyce Santos</strong><br>
                    Advogada Especialista em Direito do Consumidor<br>
                    OAB/SP 123.456<br>
                    Email: santosjoyce56@gmail.com<br>
                    Telefone: (11) 99999-9999</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Joyce Santos <santosjoyce56@gmail.com>'
    ];
    
    return mail($email, $subject, $body, implode("\r\n", $headers));
}
?>
