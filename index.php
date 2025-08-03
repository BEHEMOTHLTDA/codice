<?php
/**
 * Códice do Criador - Página Inicial
 * 
 * Página de entrada da aplicação. Se o usuário estiver logado,
 * redireciona para o dashboard. Caso contrário, exibe a landing page.
 * 
 * @author Manus AI
 * @version 1.0
 */

require_once 'php_includes/config.php';

// Verificar se o usuário está logado
if (isLoggedIn()) {
    redirect('dashboard.php');
    exit;
}

// Verificar token de "lembrar-me"
$user = new User();
if ($user->checkRememberToken()) {
    redirect('dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_DESCRIPTION; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta name="keywords" content="<?php echo SITE_KEYWORDS; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Meta tags para redes sociais -->
    <meta property="og:title" content="<?php echo SITE_NAME; ?>">
    <meta property="og:description" content="<?php echo SITE_DESCRIPTION; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo BASE_URL; ?>">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo SITE_NAME; ?>">
    <meta name="twitter:description" content="<?php echo SITE_DESCRIPTION; ?>">
</head>
<body>
    <!-- Header -->
    <header class="navbar">
        <div class="container">
            <div class="flex items-center justify-between">
                <div class="navbar-brand">
                    <h1 class="m-0 text-primary">Códice do Criador</h1>
                </div>
                
                <nav class="navbar-nav">
                    <a href="#features" class="navbar-link">Recursos</a>
                    <a href="#pricing" class="navbar-link">Preços</a>
                    <a href="#about" class="navbar-link">Sobre</a>
                    <div class="flex items-center gap-3">
                        <a href="login.php" class="btn btn-ghost">Entrar</a>
                        <a href="register.php" class="btn btn-primary">Começar Grátis</a>
                        <div class="theme-toggle" data-tooltip="Alternar tema"></div>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" style="padding: var(--space-24) 0; background: linear-gradient(135deg, var(--color-primary-50) 0%, var(--color-background) 100%);">
        <div class="container">
            <div class="grid grid-cols-2 gap-8 items-center">
                <div class="animate-on-scroll">
                    <h1 class="text-6xl font-bold mb-6">
                        Construa Mundos <span class="text-primary">Extraordinários</span>
                    </h1>
                    <p class="text-xl text-secondary mb-8">
                        A plataforma completa para criadores de mundos, escritores e mestres de RPG. 
                        Organize suas ideias, desenvolva personagens complexos e crie universos únicos 
                        com ferramentas profissionais e design elegante.
                    </p>
                    <div class="flex gap-4">
                        <a href="register.php" class="btn btn-primary btn-lg">
                            Começar Gratuitamente
                        </a>
                        <a href="#demo" class="btn btn-secondary btn-lg">
                            Ver Demonstração
                        </a>
                    </div>
                    <div class="flex items-center gap-6 mt-8 text-sm text-muted">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-success rounded-full"></span>
                            Grátis para começar
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-success rounded-full"></span>
                            Sem cartão de crédito
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 bg-success rounded-full"></span>
                            Cancele a qualquer momento
                        </div>
                    </div>
                </div>
                
                <div class="animate-on-scroll" style="animation-delay: 0.2s;">
                    <div class="card shadow-xl">
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-8 h-8 bg-primary rounded-full"></div>
                                    <div>
                                        <div class="font-semibold">Reino de Eldoria</div>
                                        <div class="text-sm text-muted">Mundo de fantasia medieval</div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 mb-4">
                                    <div class="bg-surface p-3 rounded-lg text-center">
                                        <div class="font-bold text-primary">127</div>
                                        <div class="text-xs text-muted">Personagens</div>
                                    </div>
                                    <div class="bg-surface p-3 rounded-lg text-center">
                                        <div class="font-bold text-primary">43</div>
                                        <div class="text-xs text-muted">Locais</div>
                                    </div>
                                    <div class="bg-surface p-3 rounded-lg text-center">
                                        <div class="font-bold text-primary">89</div>
                                        <div class="text-xs text-muted">Eventos</div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="w-2 h-2 bg-warning rounded-full"></span>
                                        A Grande Guerra dos Dragões
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="w-2 h-2 bg-info rounded-full"></span>
                                        Lorde Aldric, o Protetor
                                    </div>
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="w-2 h-2 bg-success rounded-full"></span>
                                        Cidade de Pedraverde
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24">
        <div class="container">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4">Recursos Poderosos</h2>
                <p class="text-xl text-secondary max-w-2xl mx-auto">
                    Tudo que você precisa para dar vida aos seus mundos e histórias, 
                    em uma interface elegante e intuitiva.
                </p>
            </div>
            
            <div class="grid grid-cols-3 gap-8">
                <div class="card animate-on-scroll">
                    <div class="card-body text-center">
                        <div class="w-16 h-16 bg-primary-light rounded-xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🌍</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Construtor de Mundos</h3>
                        <p class="text-secondary">
                            Organize personagens, locais, eventos e muito mais com categorias 
                            personalizáveis e sistema de interconexão wiki-style.
                        </p>
                    </div>
                </div>
                
                <div class="card animate-on-scroll" style="animation-delay: 0.1s;">
                    <div class="card-body text-center">
                        <div class="w-16 h-16 bg-primary-light rounded-xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">✍️</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Estúdio de Escrita</h3>
                        <p class="text-secondary">
                            Editor profissional com templates para livros, roteiros e aventuras de RPG. 
                            Modo foco e estatísticas de escrita incluídos.
                        </p>
                    </div>
                </div>
                
                <div class="card animate-on-scroll" style="animation-delay: 0.2s;">
                    <div class="card-body text-center">
                        <div class="w-16 h-16 bg-primary-light rounded-xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🎲</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Sistemas de RPG</h3>
                        <p class="text-secondary">
                            Documente e organize seus sistemas de RPG personalizados com 
                            seções estruturadas para mecânicas, classes e equipamentos.
                        </p>
                    </div>
                </div>
                
                <div class="card animate-on-scroll" style="animation-delay: 0.3s;">
                    <div class="card-body text-center">
                        <div class="w-16 h-16 bg-primary-light rounded-xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🗺️</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Mapas Interativos</h3>
                        <p class="text-secondary">
                            Crie mapas clicáveis vinculando locais aos seus artigos. 
                            Visualize seu mundo de forma interativa e imersiva.
                        </p>
                    </div>
                </div>
                
                <div class="card animate-on-scroll" style="animation-delay: 0.4s;">
                    <div class="card-body text-center">
                        <div class="w-16 h-16 bg-primary-light rounded-xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">⏰</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Linhas do Tempo</h3>
                        <p class="text-secondary">
                            Organize eventos históricos em linhas do tempo visuais. 
                            Mantenha a cronologia do seu mundo sempre clara.
                        </p>
                    </div>
                </div>
                
                <div class="card animate-on-scroll" style="animation-delay: 0.5s;">
                    <div class="card-body text-center">
                        <div class="w-16 h-16 bg-primary-light rounded-xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl">🤖</span>
                        </div>
                        <h3 class="text-xl font-semibold mb-3">Assistente de IA</h3>
                        <p class="text-secondary">
                            Gere nomes, ideias e receba auxílio na escrita com nosso 
                            assistente de IA integrado e discreto.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Collaboration Section -->
    <section class="py-24 bg-surface">
        <div class="container">
            <div class="grid grid-cols-2 gap-12 items-center">
                <div class="animate-on-scroll">
                    <h2 class="text-4xl font-bold mb-6">Colabore com Sua Equipe</h2>
                    <p class="text-lg text-secondary mb-6">
                        Convide outros criadores para trabalhar em seus mundos. 
                        Defina permissões granulares e mantenha o controle total 
                        sobre quem pode ver, editar ou administrar seu conteúdo.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 bg-success rounded-full flex items-center justify-center">
                                <span class="text-white text-xs">✓</span>
                            </div>
                            <span>Convites por e-mail</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 bg-success rounded-full flex items-center justify-center">
                                <span class="text-white text-xs">✓</span>
                            </div>
                            <span>Três níveis de permissão</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-6 h-6 bg-success rounded-full flex items-center justify-center">
                                <span class="text-white text-xs">✓</span>
                            </div>
                            <span>Controle total do proprietário</span>
                        </div>
                    </div>
                </div>
                
                <div class="animate-on-scroll" style="animation-delay: 0.2s;">
                    <div class="card shadow-lg">
                        <div class="card-header">
                            <h4 class="font-semibold">Colaboradores - Reino de Eldoria</h4>
                        </div>
                        <div class="card-body">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                            A
                                        </div>
                                        <div>
                                            <div class="font-medium">Ana Silva</div>
                                            <div class="text-sm text-muted">ana@email.com</div>
                                        </div>
                                    </div>
                                    <span class="badge badge-primary">Proprietária</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-secondary rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                            B
                                        </div>
                                        <div>
                                            <div class="font-medium">Bruno Costa</div>
                                            <div class="text-sm text-muted">bruno@email.com</div>
                                        </div>
                                    </div>
                                    <span class="badge badge-secondary">Editor</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-warning rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                            C
                                        </div>
                                        <div>
                                            <div class="font-medium">Carlos Mendes</div>
                                            <div class="text-sm text-muted">carlos@email.com</div>
                                        </div>
                                    </div>
                                    <span class="badge badge-warning">Leitor</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary btn-sm w-full">
                                Convidar Colaborador
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24">
        <div class="container">
            <div class="text-center">
                <h2 class="text-4xl font-bold mb-6">Pronto para Começar?</h2>
                <p class="text-xl text-secondary mb-8 max-w-2xl mx-auto">
                    Junte-se a milhares de criadores que já estão usando o Códice do Criador 
                    para dar vida aos seus mundos e histórias.
                </p>
                <div class="flex gap-4 justify-center">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        Criar Conta Gratuita
                    </a>
                    <a href="login.php" class="btn btn-secondary btn-lg">
                        Já Tenho Conta
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-surface border-t">
        <div class="container">
            <div class="grid grid-cols-4 gap-8">
                <div>
                    <h3 class="font-bold text-primary mb-4">Códice do Criador</h3>
                    <p class="text-sm text-secondary">
                        A plataforma completa para construção de mundos e escrita criativa.
                    </p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-3">Produto</h4>
                    <div class="space-y-2 text-sm">
                        <a href="#" class="block text-secondary hover:text-primary">Recursos</a>
                        <a href="#" class="block text-secondary hover:text-primary">Preços</a>
                        <a href="#" class="block text-secondary hover:text-primary">Atualizações</a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-3">Suporte</h4>
                    <div class="space-y-2 text-sm">
                        <a href="#" class="block text-secondary hover:text-primary">Documentação</a>
                        <a href="#" class="block text-secondary hover:text-primary">Tutoriais</a>
                        <a href="#" class="block text-secondary hover:text-primary">Contato</a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-3">Empresa</h4>
                    <div class="space-y-2 text-sm">
                        <a href="#" class="block text-secondary hover:text-primary">Sobre</a>
                        <a href="#" class="block text-secondary hover:text-primary">Blog</a>
                        <a href="#" class="block text-secondary hover:text-primary">Privacidade</a>
                    </div>
                </div>
            </div>
            
            <div class="border-t mt-8 pt-8 text-center text-sm text-muted">
                <p>&copy; 2025 Códice do Criador. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>
</html>

