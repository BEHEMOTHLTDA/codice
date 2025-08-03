<?php
/**
 * Códice do Criador - Criar Artigo
 * 
 * Página para criação de novos artigos com editor avançado
 * e sistema de interconexão wiki-style.
 * 
 * @author Manus AI
 * @version 1.0
 */

require_once 'php_includes/config.php';

// Verificar se o usuário está logado
if (!isLoggedIn()) {
    redirect('login.php');
    exit;
}

$currentUser = getCurrentUser();
if (!$currentUser) {
    redirect('login.php');
    exit;
}

// Obter ID do mundo
$worldId = (int)($_GET['world_id'] ?? 0);
if (!$worldId) {
    setFlashMessage('error', 'Mundo não especificado.');
    redirect('dashboard.php');
    exit;
}

// Verificar se o mundo existe e se o usuário tem permissão
$world = new World();
$worldData = $world->getById($worldId);

if (!$worldData) {
    setFlashMessage('error', 'Mundo não encontrado.');
    redirect('dashboard.php');
    exit;
}

// Verificar permissões (dono ou colaborador com permissão de escrita)
if ($worldData['user_id'] != $currentUser['id']) {
    $db = Database::getInstance();
    $collaboration = $db->selectOne(
        "SELECT permission_level FROM collaborators WHERE world_id = ? AND user_id = ? AND is_active = 1",
        [$worldId, $currentUser['id']]
    );
    
    if (!$collaboration || $collaboration['permission_level'] === 'reader') {
        setFlashMessage('error', 'Você não tem permissão para criar artigos neste mundo.');
        redirect('world-view.php?id=' . $worldId);
        exit;
    }
}

// Obter categorias do mundo
$categories = $world->getCategories($worldId);

// Título pré-preenchido (se vier de um link wiki)
$prefilledTitle = sanitizeInput($_GET['title'] ?? '');

$error = '';
$success = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleData = [
        'world_id' => $worldId,
        'title' => sanitizeInput($_POST['title'] ?? ''),
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'content_public' => sanitizeInput($_POST['content_public'] ?? ''),
        'content_private' => sanitizeInput($_POST['content_private'] ?? ''),
        'is_published' => isset($_POST['is_published'])
    ];
    
    // Campos personalizados (se implementados)
    $customFields = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'custom_field_') === 0) {
            $fieldId = str_replace('custom_field_', '', $key);
            $customFields[$fieldId] = sanitizeInput($value);
        }
    }
    
    if (!empty($customFields)) {
        $articleData['custom_fields'] = $customFields;
    }
    
    $article = new Article();
    $result = $article->create($articleData);
    
    if ($result['success']) {
        setFlashMessage('success', $result['message']);
        redirect('article-view.php?id=' . $result['article_id']);
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Artigo - <?php echo htmlspecialchars($worldData['name']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Criar novo artigo no mundo <?php echo htmlspecialchars($worldData['name']); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body data-world-id="<?php echo $worldId; ?>">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="text-primary font-bold">Códice do Criador</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link">
                <span>📊</span> Dashboard
            </a>
            <a href="worlds.php" class="sidebar-link">
                <span>🌍</span> Meus Mundos
            </a>
            <a href="world-view.php?id=<?php echo $worldId; ?>" class="sidebar-link active">
                <span>📖</span> <?php echo htmlspecialchars($worldData['name']); ?>
            </a>
            <a href="stories.php" class="sidebar-link">
                <span>✍️</span> Minhas Histórias
            </a>
            <a href="rpg-systems.php" class="sidebar-link">
                <span>🎲</span> Sistemas de RPG
            </a>
            <a href="profile.php" class="sidebar-link">
                <span>👤</span> Perfil
            </a>
            <a href="settings.php" class="sidebar-link">
                <span>⚙️</span> Configurações
            </a>
            <a href="logout.php" class="sidebar-link">
                <span>🚪</span> Sair
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content with-sidebar">
        <!-- Top Bar -->
        <header class="navbar">
            <div class="container-fluid">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button class="sidebar-toggle btn btn-ghost btn-icon">
                            <span>☰</span>
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold">Criar Artigo</h1>
                            <p class="text-sm text-muted">
                                Mundo: <a href="world-view.php?id=<?php echo $worldId; ?>" class="text-primary hover:text-primary-hover"><?php echo htmlspecialchars($worldData['name']); ?></a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="theme-toggle" data-tooltip="Alternar tema"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="font-medium"><?php echo htmlspecialchars($currentUser['username']); ?></div>
                                <div class="text-sm text-muted"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="container-fluid py-6">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <div class="flex items-center gap-2 text-sm text-muted">
                    <a href="dashboard.php" class="hover:text-primary">Dashboard</a>
                    <span>›</span>
                    <a href="worlds.php" class="hover:text-primary">Meus Mundos</a>
                    <span>›</span>
                    <a href="world-view.php?id=<?php echo $worldId; ?>" class="hover:text-primary"><?php echo htmlspecialchars($worldData['name']); ?></a>
                    <span>›</span>
                    <span class="text-primary">Criar Artigo</span>
                </div>
            </nav>

            <!-- Mensagem de erro -->
            <?php if ($error): ?>
                <div class="alert alert-error mb-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Editor Container -->
            <div class="editor-container">
                <form method="POST" action="" id="article-form">
                    <!-- Header do Editor -->
                    <div class="editor-header bg-surface border-b p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-primary-light rounded-lg flex items-center justify-center">
                                    <span class="text-primary text-xl">📝</span>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold">Novo Artigo</h2>
                                    <p class="text-sm text-muted">Crie conteúdo para seu mundo</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <button type="button" id="preview-article" class="btn btn-ghost">
                                    👁️ Preview
                                </button>
                                <button type="submit" id="save-article" class="btn btn-primary">
                                    💾 Salvar
                                </button>
                                <button type="submit" name="is_published" value="1" id="publish-article" class="btn btn-success">
                                    🚀 Publicar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Editor Content -->
                    <div class="editor-content">
                        <div class="grid grid-cols-4 gap-6 p-6">
                            <!-- Main Editor -->
                            <div class="col-span-3">
                                <!-- Título -->
                                <div class="form-group mb-6">
                                    <input 
                                        type="text" 
                                        id="article-title" 
                                        name="title" 
                                        class="form-input text-2xl font-bold border-0 bg-transparent p-0" 
                                        placeholder="Título do artigo..."
                                        value="<?php echo htmlspecialchars($prefilledTitle); ?>"
                                        required
                                        autofocus
                                        maxlength="200"
                                    >
                                </div>

                                <!-- Toolbar -->
                                <div class="editor-toolbar bg-surface border rounded-lg p-3 mb-4 flex items-center gap-2">
                                    <!-- Toolbar será preenchida pelo JavaScript -->
                                </div>

                                <!-- Conteúdo Público -->
                                <div class="form-group mb-6">
                                    <label for="article-content" class="form-label">Conteúdo Público</label>
                                    <textarea 
                                        id="article-content" 
                                        name="content_public" 
                                        class="form-textarea" 
                                        rows="20"
                                        placeholder="Escreva o conteúdo do seu artigo aqui...

Use @[Nome do Artigo] para criar links para outros artigos.

Formatação suportada:
# Título 1
## Título 2
### Título 3
**negrito**
*itálico*
`código`"
                                    ></textarea>
                                    <div class="form-help">
                                        Este conteúdo será visível para todos os colaboradores do mundo.
                                    </div>
                                </div>

                                <!-- Conteúdo Privado -->
                                <div class="form-group">
                                    <label for="content-private" class="form-label">Notas Privadas (Opcional)</label>
                                    <textarea 
                                        id="content-private" 
                                        name="content_private" 
                                        class="form-textarea" 
                                        rows="5"
                                        placeholder="Notas pessoais, ideias, rascunhos... Este conteúdo é visível apenas para você."
                                    ></textarea>
                                    <div class="form-help">
                                        Conteúdo privado visível apenas para você.
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-span-1">
                                <!-- Categoria -->
                                <div class="card mb-6">
                                    <div class="card-header">
                                        <h3 class="text-lg font-semibold">Categoria</h3>
                                    </div>
                                    <div class="card-body">
                                        <select id="article-category" name="category_id" class="form-select" required>
                                            <option value="">Selecione uma categoria</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="card mb-6">
                                    <div class="card-header">
                                        <h3 class="text-lg font-semibold">Status</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="editor-status text-sm text-muted mb-3">
                                            Novo artigo
                                        </div>
                                        <div id="word-count" class="text-sm text-muted">
                                            0 palavras, 0 caracteres
                                        </div>
                                    </div>
                                </div>

                                <!-- Links Referenciados -->
                                <div class="card mb-6">
                                    <div class="card-header">
                                        <h3 class="text-lg font-semibold">Links Wiki</h3>
                                    </div>
                                    <div class="card-body">
                                        <div id="referenced-links">
                                            <div class="text-sm text-muted">Nenhum link referenciado.</div>
                                        </div>
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-ghost btn-sm w-full" onclick="ArticleEditor.insertWikiLink()">
                                                🔗 Inserir Link Wiki
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dicas -->
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="text-lg font-semibold">💡 Dicas</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-sm space-y-2">
                                            <div><strong>Ctrl+S:</strong> Salvar</div>
                                            <div><strong>Ctrl+B:</strong> Negrito</div>
                                            <div><strong>Ctrl+I:</strong> Itálico</div>
                                            <div><strong>Ctrl+K:</strong> Link Wiki</div>
                                            <div class="mt-3 pt-3 border-t">
                                                <div><strong>@[Nome]:</strong> Link para artigo</div>
                                                <div><strong>**texto**:</strong> Negrito</div>
                                                <div><strong>*texto*:</strong> Itálico</div>
                                                <div><strong># Título:</strong> Cabeçalho</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    <script src="js/editor.js"></script>
    
    <!-- Script específico -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar sidebar como aberta
            if (window.innerWidth >= 768) {
                CodiceCriador.state.sidebarOpen = true;
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.add('open');
                }
            }
            
            // Focar no título se não estiver pré-preenchido
            const titleField = document.getElementById('article-title');
            if (titleField && !titleField.value.trim()) {
                titleField.focus();
            }
            
            // Validação adicional do formulário
            const form = document.getElementById('article-form');
            form.addEventListener('submit', function(e) {
                const title = document.getElementById('article-title').value.trim();
                const category = document.getElementById('article-category').value;
                const content = document.getElementById('article-content').value.trim();
                
                if (!title) {
                    e.preventDefault();
                    CodiceCriador.showNotification('O título é obrigatório.', 'error');
                    document.getElementById('article-title').focus();
                    return;
                }
                
                if (!category) {
                    e.preventDefault();
                    CodiceCriador.showNotification('Selecione uma categoria.', 'error');
                    document.getElementById('article-category').focus();
                    return;
                }
                
                if (!content) {
                    e.preventDefault();
                    CodiceCriador.showNotification('O conteúdo é obrigatório.', 'error');
                    document.getElementById('article-content').focus();
                    return;
                }
            });
        });
    </script>
</body>
</html>

