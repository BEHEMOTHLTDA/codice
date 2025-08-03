<?php
/**
 * Códice do Criador - Criar Mundo
 * 
 * Página para criação de novos mundos com formulário
 * elegante e validação completa.
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

// Verificar se o usuário pode criar mais mundos
$user = new User();
if (!$user->canCreateWorld($currentUser['id'])) {
    setFlashMessage('error', 'Você atingiu o limite máximo de mundos (' . MAX_WORLDS_PER_USER . ').');
    redirect('dashboard.php');
    exit;
}

$error = '';
$success = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    
    if (empty($name)) {
        $error = 'O nome do mundo é obrigatório.';
    } else {
        $world = new World();
        $result = $world->create($currentUser['id'], $name, $description);
        
        if ($result['success']) {
            setFlashMessage('success', $result['message']);
            redirect('world-view.php?id=' . $result['world_id']);
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Mundo - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Crie um novo mundo no <?php echo SITE_NAME; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2 class="text-primary font-bold">Códice do Criador</h2>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link">
                <span>📊</span> Dashboard
            </a>
            <a href="worlds.php" class="sidebar-link active">
                <span>🌍</span> Meus Mundos
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
                            <h1 class="text-2xl font-bold">Criar Novo Mundo</h1>
                            <p class="text-sm text-muted">Dê vida ao seu universo criativo</p>
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
        <div class="container-fluid py-8">
            <div class="max-w-2xl mx-auto">
                <!-- Breadcrumb -->
                <nav class="mb-8">
                    <div class="flex items-center gap-2 text-sm text-muted">
                        <a href="dashboard.php" class="hover:text-primary">Dashboard</a>
                        <span>›</span>
                        <a href="worlds.php" class="hover:text-primary">Meus Mundos</a>
                        <span>›</span>
                        <span class="text-primary">Criar Mundo</span>
                    </div>
                </nav>

                <!-- Form Card -->
                <div class="card shadow-lg animate-slide-up">
                    <div class="card-header">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-primary-light rounded-xl flex items-center justify-center mx-auto mb-4">
                                <span class="text-3xl">🌍</span>
                            </div>
                            <h2 class="text-2xl font-bold mb-2">Criar Novo Mundo</h2>
                            <p class="text-secondary">
                                Comece definindo o nome e uma breve descrição do seu mundo. 
                                Você poderá adicionar mais detalhes depois.
                            </p>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Limite de mundos -->
                        <div class="alert alert-info mb-6">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">ℹ️</span>
                                <div>
                                    <div class="font-semibold">Limite de Mundos</div>
                                    <div class="text-sm">
                                        Você pode criar até <?php echo MAX_WORLDS_PER_USER; ?> mundos. 
                                        Atualmente você tem <?php echo $currentUser['world_count']; ?> mundo(s).
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensagem de erro -->
                        <?php if ($error): ?>
                            <div class="alert alert-error mb-6">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulário -->
                        <form method="POST" action="" class="space-y-6" id="createWorldForm">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    Nome do Mundo *
                                </label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    class="form-input" 
                                    placeholder="Ex: Reino de Eldoria, Terra Média, Cyberpunk 2177..."
                                    value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                    required
                                    autofocus
                                    maxlength="100"
                                >
                                <div class="form-help">
                                    Escolha um nome único e memorável para seu mundo.
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description" class="form-label">
                                    Descrição
                                </label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    class="form-textarea" 
                                    placeholder="Descreva brevemente seu mundo: gênero, ambientação, características principais..."
                                    rows="4"
                                    maxlength="500"
                                ><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                                <div class="form-help">
                                    Uma breve descrição ajuda você e seus colaboradores a entenderem o contexto do mundo.
                                </div>
                            </div>

                            <!-- Preview das categorias padrão -->
                            <div class="form-group">
                                <label class="form-label">Categorias Padrão</label>
                                <div class="bg-surface p-4 rounded-lg">
                                    <p class="text-sm text-muted mb-3">
                                        Seu mundo será criado com estas categorias padrão. Você pode adicionar mais categorias depois.
                                    </p>
                                    <div class="grid grid-cols-4 gap-3">
                                        <div class="flex items-center gap-2 text-sm">
                                            <span>👤</span> Personagens
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <span>🏛️</span> Locais
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <span>⚔️</span> Itens
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <span>🐉</span> Criaturas
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <span>📜</span> História
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <span>🏴</span> Facções
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <span>⚡</span> Eventos
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <span>💡</span> Conceitos
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="flex gap-4 pt-4">
                                <button type="submit" class="btn btn-primary btn-lg flex-1">
                                    <span>🌍</span>
                                    Criar Mundo
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary btn-lg">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Dicas -->
                <div class="mt-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-lg font-semibold">💡 Dicas para Criar um Mundo</h3>
                        </div>
                        <div class="card-body">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-semibold mb-2">Nome do Mundo</h4>
                                    <ul class="text-sm text-secondary space-y-1">
                                        <li>• Seja criativo e único</li>
                                        <li>• Reflita o tom e gênero</li>
                                        <li>• Evite nomes muito complexos</li>
                                        <li>• Considere a pronúncia</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Descrição</h4>
                                    <ul class="text-sm text-secondary space-y-1">
                                        <li>• Mencione o gênero (fantasia, sci-fi, etc.)</li>
                                        <li>• Descreva a ambientação</li>
                                        <li>• Inclua elementos únicos</li>
                                        <li>• Mantenha conciso mas informativo</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    
    <!-- Script específico -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('createWorldForm');
            const nameField = document.getElementById('name');
            const descriptionField = document.getElementById('description');
            
            // Contador de caracteres para descrição
            const charCounter = document.createElement('div');
            charCounter.className = 'text-xs text-muted mt-1';
            descriptionField.parentElement.appendChild(charCounter);
            
            function updateCharCounter() {
                const current = descriptionField.value.length;
                const max = 500;
                charCounter.textContent = `${current}/${max} caracteres`;
                
                if (current > max * 0.9) {
                    charCounter.className = 'text-xs text-warning mt-1';
                } else {
                    charCounter.className = 'text-xs text-muted mt-1';
                }
            }
            
            descriptionField.addEventListener('input', updateCharCounter);
            updateCharCounter();
            
            // Validação em tempo real do nome
            nameField.addEventListener('input', function() {
                const value = this.value.trim();
                
                if (value.length > 0 && value.length < 3) {
                    this.classList.add('error');
                    CodiceCriador.showFieldError(this, 'O nome deve ter pelo menos 3 caracteres.');
                } else {
                    this.classList.remove('error');
                    CodiceCriador.clearFieldError(this);
                }
            });
            
            // Sugestões de nomes baseadas no que o usuário digita
            const suggestions = [
                'Reino de', 'Terra de', 'Mundo de', 'Império de', 'Continente de',
                'Dimensão', 'Universo', 'Galáxia', 'Planeta', 'Cidade de'
            ];
            
            nameField.addEventListener('input', function() {
                // Remover sugestões anteriores
                const existingSuggestions = document.querySelector('.name-suggestions');
                if (existingSuggestions) {
                    existingSuggestions.remove();
                }
                
                const value = this.value.trim().toLowerCase();
                if (value.length >= 2) {
                    const matchingSuggestions = suggestions.filter(s => 
                        s.toLowerCase().includes(value) || value.includes(s.toLowerCase())
                    );
                    
                    if (matchingSuggestions.length > 0) {
                        const suggestionsDiv = document.createElement('div');
                        suggestionsDiv.className = 'name-suggestions mt-2 p-3 bg-surface rounded-lg border';
                        suggestionsDiv.innerHTML = `
                            <div class="text-xs text-muted mb-2">Sugestões:</div>
                            <div class="flex flex-wrap gap-2">
                                ${matchingSuggestions.slice(0, 3).map(s => 
                                    `<button type="button" class="btn btn-ghost btn-sm suggestion-btn" data-suggestion="${s}">${s}</button>`
                                ).join('')}
                            </div>
                        `;
                        
                        this.parentElement.appendChild(suggestionsDiv);
                        
                        // Adicionar eventos aos botões de sugestão
                        suggestionsDiv.querySelectorAll('.suggestion-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                nameField.value = this.dataset.suggestion + ' ';
                                nameField.focus();
                                suggestionsDiv.remove();
                            });
                        });
                    }
                }
            });
            
            // Validação final do formulário
            form.addEventListener('submit', function(e) {
                const name = nameField.value.trim();
                
                if (!name || name.length < 3) {
                    e.preventDefault();
                    CodiceCriador.showNotification('O nome do mundo deve ter pelo menos 3 caracteres.', 'error');
                    nameField.focus();
                    return;
                }
                
                if (name.length > 100) {
                    e.preventDefault();
                    CodiceCriador.showNotification('O nome do mundo não pode ter mais de 100 caracteres.', 'error');
                    nameField.focus();
                    return;
                }
            });
            
            // Inicializar sidebar como aberta
            if (window.innerWidth >= 768) {
                CodiceCriador.state.sidebarOpen = true;
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.add('open');
                }
            }
        });
    </script>
</body>
</html>

