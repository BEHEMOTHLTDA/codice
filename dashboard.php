<?php
/**
 * Códice do Criador - Dashboard Principal
 * 
 * Painel principal da aplicação onde o usuário visualiza
 * seus mundos, histórias e sistemas de RPG.
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

// Obter dados do usuário atual
$currentUser = getCurrentUser();
if (!$currentUser) {
    redirect('login.php');
    exit;
}

$db = Database::getInstance();

// Buscar mundos do usuário
$worlds = $db->select(
    "SELECT w.*, 
            (SELECT COUNT(*) FROM articles WHERE world_id = w.id) as article_count,
            (SELECT COUNT(*) FROM collaborators WHERE world_id = w.id AND is_active = 1) as collaborator_count
     FROM worlds w 
     WHERE w.user_id = ? 
     ORDER BY w.updated_at DESC",
    [$currentUser['id']]
);

// Buscar histórias do usuário
$stories = $db->select(
    "SELECT s.*, 
            (SELECT COUNT(*) FROM story_chapters WHERE story_id = s.id) as chapter_count
     FROM stories s 
     WHERE s.user_id = ? 
     ORDER BY s.updated_at DESC",
    [$currentUser['id']]
);

// Buscar sistemas de RPG do usuário
$rpgSystems = $db->select(
    "SELECT r.*, 
            (SELECT COUNT(*) FROM rpg_system_sections WHERE rpg_system_id = r.id) as section_count
     FROM rpg_systems r 
     WHERE r.user_id = ? 
     ORDER BY r.updated_at DESC",
    [$currentUser['id']]
);

// Estatísticas gerais
$totalArticles = $db->selectOne(
    "SELECT COUNT(*) as total FROM articles a 
     JOIN worlds w ON a.world_id = w.id 
     WHERE w.user_id = ?",
    [$currentUser['id']]
)['total'] ?? 0;

$totalWords = $db->selectOne(
    "SELECT SUM(word_count) as total FROM stories WHERE user_id = ?",
    [$currentUser['id']]
)['total'] ?? 0;

// Atividade recente
$recentActivity = $db->select(
    "SELECT 'article' as type, a.title, a.updated_at, w.name as world_name
     FROM articles a 
     JOIN worlds w ON a.world_id = w.id 
     WHERE w.user_id = ?
     UNION ALL
     SELECT 'story' as type, s.title, s.updated_at, NULL as world_name
     FROM stories s 
     WHERE s.user_id = ?
     UNION ALL
     SELECT 'world' as type, w.name as title, w.updated_at, NULL as world_name
     FROM worlds w 
     WHERE w.user_id = ?
     ORDER BY updated_at DESC 
     LIMIT 10",
    [$currentUser['id'], $currentUser['id'], $currentUser['id']]
);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Painel principal do <?php echo SITE_NAME; ?>">
    
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
            <a href="dashboard.php" class="sidebar-link active">
                <span>📊</span> Dashboard
            </a>
            <a href="worlds.php" class="sidebar-link">
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
                        <h1 class="text-2xl font-bold">Dashboard</h1>
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

        <!-- Dashboard Content -->
        <div class="container-fluid py-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold mb-2">
                    Bem-vindo de volta, <?php echo htmlspecialchars($currentUser['username']); ?>! 👋
                </h2>
                <p class="text-secondary">
                    Continue criando mundos extraordinários e histórias envolventes.
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-4 gap-6 mb-8">
                <div class="card animate-on-scroll">
                    <div class="card-body text-center">
                        <div class="text-3xl font-bold text-primary mb-2"><?php echo count($worlds); ?></div>
                        <div class="text-sm text-muted">Mundos Criados</div>
                        <div class="text-xs text-muted mt-1">
                            Limite: <?php echo MAX_WORLDS_PER_USER; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card animate-on-scroll" style="animation-delay: 0.1s;">
                    <div class="card-body text-center">
                        <div class="text-3xl font-bold text-primary mb-2"><?php echo count($stories); ?></div>
                        <div class="text-sm text-muted">Histórias</div>
                        <div class="text-xs text-muted mt-1">
                            Limite: <?php echo MAX_STORIES_PER_USER; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card animate-on-scroll" style="animation-delay: 0.2s;">
                    <div class="card-body text-center">
                        <div class="text-3xl font-bold text-primary mb-2"><?php echo $totalArticles; ?></div>
                        <div class="text-sm text-muted">Artigos Totais</div>
                        <div class="text-xs text-muted mt-1">
                            Em todos os mundos
                        </div>
                    </div>
                </div>
                
                <div class="card animate-on-scroll" style="animation-delay: 0.3s;">
                    <div class="card-body text-center">
                        <div class="text-3xl font-bold text-primary mb-2"><?php echo number_format($totalWords); ?></div>
                        <div class="text-sm text-muted">Palavras Escritas</div>
                        <div class="text-xs text-muted mt-1">
                            Em todas as histórias
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-3 gap-8">
                <!-- Mundos -->
                <div class="col-span-2">
                    <div class="card">
                        <div class="card-header">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-semibold">Meus Mundos</h3>
                                <?php if (count($worlds) < MAX_WORLDS_PER_USER): ?>
                                    <a href="world-create.php" class="btn btn-primary btn-sm">
                                        Criar Mundo
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-warning">Limite atingido</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <?php if (empty($worlds)): ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">🌍</div>
                                    <div class="empty-state-title">Nenhum mundo criado</div>
                                    <div class="empty-state-description">
                                        Crie seu primeiro mundo e comece a construir universos extraordinários.
                                    </div>
                                    <a href="world-create.php" class="btn btn-primary">
                                        Criar Primeiro Mundo
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach (array_slice($worlds, 0, 3) as $world): ?>
                                        <div class="flex items-center justify-between p-4 bg-surface rounded-lg hover:bg-surface-elevated transition-colors">
                                            <div class="flex items-center gap-4">
                                                <div class="w-12 h-12 bg-primary-light rounded-lg flex items-center justify-center">
                                                    <span class="text-primary text-xl">🌍</span>
                                                </div>
                                                <div>
                                                    <h4 class="font-semibold"><?php echo htmlspecialchars($world['name']); ?></h4>
                                                    <p class="text-sm text-muted">
                                                        <?php echo $world['article_count']; ?> artigos • 
                                                        <?php echo $world['collaborator_count']; ?> colaboradores
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="flex gap-2">
                                                <a href="world-view.php?id=<?php echo $world['id']; ?>" class="btn btn-ghost btn-sm">
                                                    Ver
                                                </a>
                                                <a href="world-edit.php?id=<?php echo $world['id']; ?>" class="btn btn-secondary btn-sm">
                                                    Editar
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($worlds) > 3): ?>
                                        <div class="text-center pt-4">
                                            <a href="worlds.php" class="btn btn-ghost">
                                                Ver todos os mundos (<?php echo count($worlds); ?>)
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Atividade Recente -->
                <div>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-xl font-semibold">Atividade Recente</h3>
                        </div>
                        
                        <div class="card-body">
                            <?php if (empty($recentActivity)): ?>
                                <div class="text-center text-muted py-8">
                                    <div class="text-4xl mb-2">📝</div>
                                    <div class="text-sm">Nenhuma atividade recente</div>
                                </div>
                            <?php else: ?>
                                <div class="space-y-3">
                                    <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 bg-primary-light rounded-full flex items-center justify-center flex-shrink-0">
                                                <?php
                                                $icon = '📝';
                                                switch ($activity['type']) {
                                                    case 'world': $icon = '🌍'; break;
                                                    case 'story': $icon = '✍️'; break;
                                                    case 'article': $icon = '📄'; break;
                                                }
                                                echo $icon;
                                                ?>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-medium truncate">
                                                    <?php echo htmlspecialchars($activity['title']); ?>
                                                </div>
                                                <?php if ($activity['world_name']): ?>
                                                    <div class="text-xs text-muted">
                                                        em <?php echo htmlspecialchars($activity['world_name']); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="text-xs text-muted">
                                                    <?php echo CodiceCriador::getRelativeTime($activity['updated_at']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Histórias e Sistemas de RPG -->
            <div class="grid grid-cols-2 gap-8 mt-8">
                <!-- Histórias -->
                <div class="card">
                    <div class="card-header">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold">Minhas Histórias</h3>
                            <?php if (count($stories) < MAX_STORIES_PER_USER): ?>
                                <a href="story-create.php" class="btn btn-primary btn-sm">
                                    Nova História
                                </a>
                            <?php else: ?>
                                <span class="badge badge-warning">Limite atingido</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($stories)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">✍️</div>
                                <div class="empty-state-title">Nenhuma história</div>
                                <div class="empty-state-description">
                                    Comece a escrever sua primeira história.
                                </div>
                                <a href="story-create.php" class="btn btn-primary">
                                    Criar História
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach (array_slice($stories, 0, 3) as $story): ?>
                                    <div class="flex items-center justify-between p-3 bg-surface rounded-lg">
                                        <div>
                                            <h4 class="font-medium"><?php echo htmlspecialchars($story['title']); ?></h4>
                                            <p class="text-sm text-muted">
                                                <?php echo $story['chapter_count']; ?> capítulos • 
                                                <?php echo number_format($story['word_count']); ?> palavras
                                            </p>
                                        </div>
                                        <a href="story-edit.php?id=<?php echo $story['id']; ?>" class="btn btn-ghost btn-sm">
                                            Editar
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($stories) > 3): ?>
                                    <div class="text-center pt-2">
                                        <a href="stories.php" class="btn btn-ghost btn-sm">
                                            Ver todas (<?php echo count($stories); ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sistemas de RPG -->
                <div class="card">
                    <div class="card-header">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-semibold">Sistemas de RPG</h3>
                            <a href="rpg-create.php" class="btn btn-primary btn-sm">
                                Novo Sistema
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <?php if (empty($rpgSystems)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">🎲</div>
                                <div class="empty-state-title">Nenhum sistema</div>
                                <div class="empty-state-description">
                                    Crie seu primeiro sistema de RPG.
                                </div>
                                <a href="rpg-create.php" class="btn btn-primary">
                                    Criar Sistema
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach (array_slice($rpgSystems, 0, 3) as $system): ?>
                                    <div class="flex items-center justify-between p-3 bg-surface rounded-lg">
                                        <div>
                                            <h4 class="font-medium"><?php echo htmlspecialchars($system['name']); ?></h4>
                                            <p class="text-sm text-muted">
                                                <?php echo $system['section_count']; ?> seções
                                            </p>
                                        </div>
                                        <a href="rpg-edit.php?id=<?php echo $system['id']; ?>" class="btn btn-ghost btn-sm">
                                            Editar
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($rpgSystems) > 3): ?>
                                    <div class="text-center pt-2">
                                        <a href="rpg-systems.php" class="btn btn-ghost btn-sm">
                                            Ver todos (<?php echo count($rpgSystems); ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    
    <!-- Script específico do dashboard -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar sidebar como aberta em telas grandes
            if (window.innerWidth >= 768) {
                CodiceCriador.state.sidebarOpen = true;
                const sidebar = document.getElementById('sidebar');
                if (sidebar) {
                    sidebar.classList.add('open');
                }
            }
            
            // Adicionar animações aos cards de estatísticas
            const statCards = document.querySelectorAll('.grid .card');
            statCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            
            // Atualizar contadores com animação
            const counters = document.querySelectorAll('.text-3xl.font-bold.text-primary');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                let current = 0;
                const increment = target / 20;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 50);
            });
        });
    </script>
</body>
</html>

