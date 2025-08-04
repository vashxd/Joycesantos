<?php
// Verificar se é primeira execução e configurar banco se necessário
if (!empty($_GET['setup']) && $_GET['setup'] === 'database') {
    header('Content-Type: text/plain');
    include 'setup_database_railway.php';
    exit;
}

// Para solicitações normais, servir o HTML
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Se for a raiz ou index, servir index.html
if ($path === '/' || $path === '/index' || $path === '/index.php') {
    header('Content-Type: text/html');
    readfile('index.html');
    exit;
}

// Se for uma API, redirecionar para o arquivo correto
if (strpos($path, '/api/') === 0) {
    $apiFile = '.' . $path . '.php';
    if (file_exists($apiFile)) {
        include $apiFile;
        exit;
    }
}

// Se for admin, redirecionar para o arquivo correto
if (strpos($path, '/admin/') === 0) {
    $adminFile = '.' . $path . '.php';
    if (file_exists($adminFile)) {
        include $adminFile;
        exit;
    }
}

// Para outros arquivos estáticos, tentar servir diretamente
$filePath = '.' . $path;
if (file_exists($filePath)) {
    // Determinar o content type
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $contentTypes = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon'
    ];
    
    if (isset($contentTypes[$extension])) {
        header('Content-Type: ' . $contentTypes[$extension]);
    }
    
    readfile($filePath);
    exit;
}

// Se nada for encontrado, servir index.html
header('Content-Type: text/html');
readfile('index.html');
?>
