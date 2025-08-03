<?php
/**
 * Códice do Criador - Página de Login
 * 
 * Página de autenticação de usuários com design elegante
 * e funcionalidades de "lembrar-me" e recuperação de senha.
 * 
 * @author Manus AI
 * @version 1.0
 */

require_once 'php_includes/config.php';

// Redirecionar se já estiver logado
if (isLoggedIn()) {
    redirect('dashboard.php');
    exit;
}

$error = '';
$success = '';

// Processar formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        $user = new User();
        $result = $user->login($email, $password, $rememberMe);
        
        if ($result['success']) {
            redirect('dashboard.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

// Verificar mensagens flash
$flashMessages = getFlashMessages();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Faça login no <?php echo SITE_NAME; ?> e acesse seus mundos e histórias.">
    
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
    <!-- Header simples -->
    <header class="navbar">
        <div class="container">
            <div class="flex items-center justify-between">
                <a href="index.php" class="navbar-brand">
                    <h1 class="m-0 text-primary">Códice do Criador</h1>
                </a>
                
                <div class="flex items-center gap-3">
                    <span class="text-secondary">Não tem conta?</span>
                    <a href="register.php" class="btn btn-primary">Criar Conta</a>
                    <div class="theme-toggle" data-tooltip="Alternar tema"></div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen flex items-center justify-center py-12" style="background: linear-gradient(135deg, var(--color-primary-50) 0%, var(--color-background) 100%);">
        <div class="container-narrow">
            <div class="card shadow-2xl animate-slide-up">
                <div class="card-body p-8">
                    <!-- Header do formulário -->
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold mb-2">Bem-vindo de volta!</h1>
                        <p class="text-secondary">Entre na sua conta para continuar criando</p>
                    </div>

                    <!-- Mensagens flash -->
                    <?php foreach ($flashMessages as $message): ?>
                        <div class="alert alert-<?php echo $message['type']; ?> mb-4">
                            <?php echo htmlspecialchars($message['message']); ?>
                        </div>
                    <?php endforeach; ?>

                    <!-- Mensagem de erro -->
                    <?php if ($error): ?>
                        <div class="alert alert-error mb-6">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulário de login -->
                    <form method="POST" action="" class="space-y-6">
                        <div class="form-group">
                            <label for="email" class="form-label">E-mail</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="seu@email.com"
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                required
                                autocomplete="email"
                                autofocus
                            >
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Senha</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Sua senha"
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="remember_me" 
                                    class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                                >
                                <span class="text-sm text-secondary">Lembrar-me</span>
                            </label>
                            
                            <a href="forgot-password.php" class="text-sm text-primary hover:text-primary-hover">
                                Esqueceu a senha?
                            </a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-full">
                            Entrar
                        </button>
                    </form>

                    <!-- Divider -->
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-surface-elevated text-muted">ou</span>
                        </div>
                    </div>

                    <!-- Link para registro -->
                    <div class="text-center">
                        <p class="text-secondary">
                            Não tem uma conta? 
                            <a href="register.php" class="text-primary hover:text-primary-hover font-medium">
                                Criar conta gratuita
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Links adicionais -->
            <div class="text-center mt-8 space-y-2">
                <div class="flex justify-center gap-6 text-sm text-muted">
                    <a href="#" class="hover:text-primary">Termos de Uso</a>
                    <a href="#" class="hover:text-primary">Política de Privacidade</a>
                    <a href="#" class="hover:text-primary">Suporte</a>
                </div>
                <p class="text-xs text-muted">
                    &copy; 2025 Códice do Criador. Todos os direitos reservados.
                </p>
            </div>
        </div>
    </main>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
    
    <!-- Script específico para login -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focar no campo de email se estiver vazio
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            
            if (emailField && !emailField.value) {
                emailField.focus();
            } else if (passwordField) {
                passwordField.focus();
            }
            
            // Validação em tempo real
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const email = emailField.value.trim();
                    const password = passwordField.value;
                    
                    if (!email || !password) {
                        e.preventDefault();
                        CodiceCriador.showNotification('Por favor, preencha todos os campos.', 'error');
                        return;
                    }
                    
                    if (!email.includes('@') || !email.includes('.')) {
                        e.preventDefault();
                        CodiceCriador.showNotification('Por favor, digite um e-mail válido.', 'error');
                        emailField.focus();
                        return;
                    }
                });
            }
            
            // Mostrar/ocultar senha
            const togglePassword = document.createElement('button');
            togglePassword.type = 'button';
            togglePassword.className = 'absolute right-3 top-1/2 transform -translate-y-1/2 text-muted hover:text-primary';
            togglePassword.innerHTML = '👁️';
            togglePassword.style.cssText = 'background: none; border: none; cursor: pointer; font-size: 16px;';
            
            const passwordContainer = passwordField.parentElement;
            passwordContainer.style.position = 'relative';
            passwordContainer.appendChild(togglePassword);
            
            togglePassword.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    togglePassword.innerHTML = '🙈';
                } else {
                    passwordField.type = 'password';
                    togglePassword.innerHTML = '👁️';
                }
            });
        });
    </script>
</body>
</html>

