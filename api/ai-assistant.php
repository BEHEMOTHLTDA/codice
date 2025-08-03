<?php
/**
 * Códice do Criador - API do Assistente de IA
 * 
 * Endpoint para comunicação com o assistente de IA,
 * processando requisições e retornando respostas JSON.
 * 
 * @author Manus AI
 * @version 1.0
 */

// Headers para API JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Tratar OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Incluir dependências
require_once '../php_includes/config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Obter dados da requisição
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

// Verificar ação
$action = $data['action'] ?? '';
if (empty($action)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ação não especificada']);
    exit;
}

// Inicializar assistente de IA
$aiAssistant = new AIAssistant();

try {
    switch ($action) {
        case 'generate_content_suggestions':
            $result = handleContentSuggestions($aiAssistant, $data);
            break;
            
        case 'generate_article_ideas':
            $result = handleArticleIdeas($aiAssistant, $data);
            break;
            
        case 'generate_names':
            $result = handleNameGeneration($aiAssistant, $data);
            break;
            
        case 'analyze_content':
            $result = handleContentAnalysis($aiAssistant, $data);
            break;
            
        case 'expand_content':
            $result = handleContentExpansion($aiAssistant, $data);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ação não reconhecida']);
            exit;
    }
    
    // Retornar resultado
    echo json_encode($result);
    
} catch (Exception $e) {
    logError("Erro na API do assistente de IA: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}

/**
 * Processar sugestões de conteúdo
 */
function handleContentSuggestions($aiAssistant, $data) {
    $title = sanitizeInput($data['title'] ?? '');
    $category = sanitizeInput($data['category'] ?? '');
    $existingContent = sanitizeInput($data['existing_content'] ?? '');
    $worldId = (int)($data['world_id'] ?? 0);
    
    if (empty($title)) {
        return [
            'success' => false,
            'message' => 'Título é obrigatório para gerar sugestões.'
        ];
    }
    
    // Verificar permissões do mundo se especificado
    if ($worldId > 0 && !hasWorldPermission($worldId, 'read')) {
        return [
            'success' => false,
            'message' => 'Você não tem permissão para acessar este mundo.'
        ];
    }
    
    // Obter contexto do mundo
    $worldContext = '';
    if ($worldId > 0) {
        $world = new World();
        $worldData = $world->getById($worldId);
        if ($worldData) {
            $worldContext = $worldData['name'];
            if ($worldData['description']) {
                $worldContext .= ': ' . $worldData['description'];
            }
        }
    }
    
    return $aiAssistant->generateContentSuggestions($title, $category, $worldContext, $existingContent);
}

/**
 * Processar ideias de artigos
 */
function handleArticleIdeas($aiAssistant, $data) {
    $category = sanitizeInput($data['category'] ?? '');
    $worldId = (int)($data['world_id'] ?? 0);
    
    if ($worldId <= 0) {
        return [
            'success' => false,
            'message' => 'ID do mundo é obrigatório.'
        ];
    }
    
    // Verificar permissões
    if (!hasWorldPermission($worldId, 'read')) {
        return [
            'success' => false,
            'message' => 'Você não tem permissão para acessar este mundo.'
        ];
    }
    
    return $aiAssistant->generateArticleIdeas($worldId, $category);
}

/**
 * Processar geração de nomes
 */
function handleNameGeneration($aiAssistant, $data) {
    $type = sanitizeInput($data['type'] ?? '');
    $context = sanitizeInput($data['context'] ?? '');
    $count = min(20, max(1, (int)($data['count'] ?? 10))); // Limitar entre 1 e 20
    
    if (empty($type)) {
        return [
            'success' => false,
            'message' => 'Tipo de nome é obrigatório.'
        ];
    }
    
    $validTypes = ['person', 'place', 'item', 'creature', 'faction'];
    if (!in_array($type, $validTypes)) {
        return [
            'success' => false,
            'message' => 'Tipo de nome inválido.'
        ];
    }
    
    return $aiAssistant->generateNames($type, $context, $count);
}

/**
 * Processar análise de conteúdo
 */
function handleContentAnalysis($aiAssistant, $data) {
    $content = sanitizeInput($data['content'] ?? '');
    $type = sanitizeInput($data['type'] ?? 'article');
    
    if (empty($content)) {
        return [
            'success' => false,
            'message' => 'Conteúdo é obrigatório para análise.'
        ];
    }
    
    if (strlen($content) < 50) {
        return [
            'success' => false,
            'message' => 'Conteúdo muito curto para análise significativa.'
        ];
    }
    
    $validTypes = ['article', 'story', 'description'];
    if (!in_array($type, $validTypes)) {
        $type = 'article';
    }
    
    return $aiAssistant->analyzeAndImprove($content, $type);
}

/**
 * Processar expansão de conteúdo
 */
function handleContentExpansion($aiAssistant, $data) {
    $content = sanitizeInput($data['content'] ?? '');
    $context = sanitizeInput($data['context'] ?? '');
    $direction = sanitizeInput($data['direction'] ?? 'general');
    
    if (empty($content)) {
        return [
            'success' => false,
            'message' => 'Conteúdo é obrigatório para expansão.'
        ];
    }
    
    if (strlen($content) < 20) {
        return [
            'success' => false,
            'message' => 'Conteúdo muito curto para expansão.'
        ];
    }
    
    return $aiAssistant->expandContent($content, $context, $direction);
}

/**
 * Verificar permissões do mundo
 */
function hasWorldPermission($worldId, $permission = 'read') {
    $currentUser = getCurrentUser();
    if (!$currentUser) return false;
    
    $db = Database::getInstance();
    
    // Verificar se é o dono do mundo
    $world = $db->selectOne("SELECT user_id FROM worlds WHERE id = ?", [$worldId]);
    if ($world && $world['user_id'] == $currentUser['id']) {
        return true;
    }
    
    // Verificar colaboração
    $collaboration = $db->selectOne(
        "SELECT permission_level FROM collaborators WHERE world_id = ? AND user_id = ? AND is_active = 1",
        [$worldId, $currentUser['id']]
    );
    
    if (!$collaboration) return false;
    
    // Verificar nível de permissão
    switch ($permission) {
        case 'read':
            return in_array($collaboration['permission_level'], ['reader', 'editor']);
        case 'write':
            return $collaboration['permission_level'] === 'editor';
        default:
            return false;
    }
}
?>

