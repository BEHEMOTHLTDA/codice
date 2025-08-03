<?php
/**
 * Códice do Criador - Arquivo de Configuração
 * * Este arquivo contém todas as configurações principais da aplicação,
 * incluindo configurações de banco de dados, segurança e constantes globais.
 * * @author Manus AI
 * @version 1.0
 */

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================================================================
// MODO DE PRODUÇÃO - AJUSTADO PARA SERVIDOR
// ==================================================================
error_reporting(0);
ini_set('display_errors', 0);
define('DEBUG_MODE', false); // Desabilitar em produção

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// ==================================================================
// CONFIGURAÇÕES DO BANCO DE DADOS - AJUSTADO PARA DOCKER
// ==================================================================
define('DB_HOST', 'db');                 // Alterado de 'localhost' para 'db' para conectar ao container do Docker
define('DB_NAME', 'codice_do_criador');  // Mantido conforme o docker-compose.yml
define('DB_USER', 'root');               // Mantido conforme o docker-compose.yml
define('DB_PASS', 'rootpassword');       // Alterado de '' para 'rootpassword' para corresponder ao docker-compose.yml
define('DB_CHARSET', 'utf8mb4');

// ==================================================================
// CONFIGURAÇÕES DE SEGURANÇA - AJUSTADO PARA PRODUÇÃO
// ==================================================================
define('SALT', 'bf9a8e7a3a311b7e52701b2ab1a4d4a8'); // Alterado para um valor aleatório e seguro
define('SESSION_LIFETIME', 3600 * 24 * 30); // 30 dias
define('PASSWORD_RESET_EXPIRY', 3600); // 1 hora

// Configurações de upload
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Configurações de limites de conta
define('MAX_WORLDS_PER_USER', 5);
define('MAX_STORIES_PER_USER', 5);
define('MAX_RPG_SYSTEMS_PER_USER', 5);

// Configurações da API Groq (para o assistente de IA)
define('GROQ_API_KEY', getenv('GROQ_API_KEY') ?: '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'); // Definir via variável de ambiente no docker-compose.yml
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');

// ==================================================================
// URLS BASE - AJUSTAR COM SEU DOMÍNIO
// ==================================================================
define('BASE_URL', 'https://codice.versallesrpg.com'); // <- IMPORTANTE: Altere para o seu domínio
define('ASSETS_URL', BASE_URL . 'assets/');

// Configurações de email (para recuperação de senha)
// IMPORTANTE: Ajuste com um serviço de e-mail real (SendGrid, Amazon SES, etc.) para funcionar
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'seu_usuario_smtp');
define('SMTP_PASSWORD', 'sua_senha_smtp');
define('FROM_EMAIL', 'noreply@seudominio.com');
define('FROM_NAME', 'Códice do Criador');

// Configurações de cache
define('CACHE_ENABLED', false); // Pode ser habilitado para performance
define('CACHE_LIFETIME', 3600);

// Configurações de log
define('LOG_ERRORS', true);
define('LOG_PATH', __DIR__ . '/../logs/');

// Configurações de paginação
define('ARTICLES_PER_PAGE', 20);
define('STORIES_PER_PAGE', 10);
define('CHAPTERS_PER_PAGE', 15);

// Configurações de tema
define('DEFAULT_THEME', 'light');
define('AVAILABLE_THEMES', ['light', 'dark']);

// Configurações de SEO
define('SITE_NAME', 'Códice do Criador');
define('SITE_DESCRIPTION', 'Plataforma completa para construção de mundos e escrita criativa');
define('SITE_KEYWORDS', 'worldbuilding, escrita, rpg, criação, mundos, histórias');

// >>> O RESTANTE DO ARQUIVO PERMANECE IGUAL (FUNÇÕES, HEADERS, ETC.) <<<

// Função para carregar configurações do banco de dados
function loadDatabaseConfig() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            die("Erro de conexão com o banco de dados: " . $e->getMessage());
        } else {
            // Esconde o erro detalhado em produção
            logError("PDOException: " . $e->getMessage());
            die("Erro interno do servidor. Não foi possível conectar ao banco de dados.");
        }
    }
}

// Função para sanitizar entrada de dados
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Função para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para gerar token seguro
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Função para verificar se o usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Função para obter o ID do usuário atual
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Função para obter dados do usuário atual
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    static $currentUser = null;
    
    if ($currentUser === null) {
        $pdo = loadDatabaseConfig();
        $stmt = $pdo->prepare("SELECT id, username, email, theme_preference, created_at FROM users WHERE id = ?");
        $stmt->execute([getCurrentUserId()]);
        $currentUser = $stmt->fetch();
    }
    
    return $currentUser;
}

// Função para redirecionar
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Função para exibir mensagens flash
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

// Função para log de erros
function logError($message, $file = null, $line = null) {
    if (!LOG_ERRORS) return;
    
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if ($file) $logMessage .= " in " . $file;
    if ($line) $logMessage .= " on line " . $line;
    $logMessage .= PHP_EOL;
    
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }
    
    file_put_contents(LOG_PATH . 'error.log', $logMessage, FILE_APPEND | LOCK_EX);
}

// Função para verificar permissões de mundo
function hasWorldPermission($worldId, $permission = 'read') {
    if (!isLoggedIn()) return false;
    
    $pdo = loadDatabaseConfig();
    $userId = getCurrentUserId();
    
    // Verificar se é o dono do mundo
    $stmt = $pdo->prepare("SELECT user_id FROM worlds WHERE id = ?");
    $stmt->execute([$worldId]);
    $world = $stmt->fetch();
    
    if ($world && $world['user_id'] == $userId) {
        return true; // Dono tem todas as permissões
    }
    
    // Verificar se é colaborador
    $stmt = $pdo->prepare("
        SELECT permission_level 
        FROM collaborators 
        WHERE world_id = ? AND user_id = ? AND is_active = 1
    ");
    $stmt->execute([$worldId, $userId]);
    $collaborator = $stmt->fetch();
    
    if (!$collaborator) return false;
    
    $userPermission = $collaborator['permission_level'];
    
    switch ($permission) {
        case 'read':
            return in_array($userPermission, ['owner', 'editor', 'reader']);
        case 'write':
            return in_array($userPermission, ['owner', 'editor']);
        case 'admin':
            return $userPermission === 'owner';
        default:
            return false;
    }
}

// Autoloader simples para classes
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Configurações de headers de segurança
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Verificar se as pastas necessárias existem
$requiredDirs = [UPLOAD_PATH, LOG_PATH];
foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
?>