<?php
/**
 * Códice do Criador - Página de Registro
 * 
 * Página de cadastro de novos usuários com validação
 * completa e design elegante.
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

// Processar formulário de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Validações básicas
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif ($password !== $confirmPassword) {
        $error = 'As senhas não coincidem.';
    } elseif (!$terms) {
        $error = 'Você deve aceitar os termos de uso.';
    } else {
        $user = new User();
        $result = $user->register($username, $email, $password);
        
        if ($result['success']) {
            $success = $result['message'];
            // Limpar campos após sucesso
            $username = $email = '';
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
    <title>Criar Conta - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Crie sua conta no <?php echo SITE_NAME; ?> e comece a construir mundos extraordinários.">
    
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
                    <span class="text-secondary">Já tem conta?</span>
                    <a href="login.php" class="btn btn-secondary">Entrar</a>
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
                        <h1 class="text-3xl font-bold mb-2">Crie sua conta</h1>
                        <p class="text-secondary">Comece a construir mundos extraordinários hoje mesmo</p>
                    </div>

                    <!-- Mensagem de sucesso -->
                    <?php if ($success): ?>
                        <div class="alert alert-success mb-6">
                            <?php echo htmlspecialchars($success); ?>
                            <div class="mt-3">
                                <a href="login.php" class="btn btn-primary btn-sm">
                                    Fazer Login Agora
                                </a>
                            </div>
                        </div>
                    <?php else: ?>

                    <!-- Mensagem de erro -->
                    <?php if ($error): ?>
                        <div class="alert alert-error mb-6">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulário de registro -->
                    <form method="POST" action="" class="space-y-6" id="registerForm">
                        <div class="form-group">
                            <label for="username" class="form-label">Nome de usuário</label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-input" 
                                placeholder="Escolha um nome único"
                                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                required
                                autocomplete="username"
                                autofocus
                                pattern="[a-zA-Z0-9_]+"
                                minlength="3"
                                maxlength="50"
                            >
                            <div class="form-help">
                                Apenas letras, números e underscore. Mínimo 3 caracteres.
                            </div>
                        </div>

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
                            >
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">Senha</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                placeholder="Crie uma senha segura"
                                required
                                autocomplete="new-password"
                                minlength="6"
                            >
                            <div class="form-help">
                                Mínimo 6 caracteres.
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirmar senha</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                placeholder="Digite a senha novamente"
                                required
                                autocomplete="new-password"
                            >
                        </div>

                        <div class="form-group">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="terms" 
                                    class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary mt-1"
                                    required
                                >
                                <span class="text-sm text-secondary">
                                    Eu aceito os 
                                    <a href="#" class="text-primary hover:text-primary-hover">Termos de Uso</a> 
                                    e a 
                                    <a href="#" class="text-primary hover:text-primary-hover">Política de Privacidade</a>
                                </span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-full">
                            Criar Conta Gratuita
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

                    <!-- Link para login -->
                    <div class="text-center">
                        <p class="text-secondary">
                            Já tem uma conta? 
                            <a href="login.php" class="text-primary hover:text-primary-hover font-medium">
                                Fazer login
                            </a>
                        </p>
                    </div>

                    <?php endif; ?>
                </div>
            </div>

            <!-- Benefícios -->
            <div class="grid grid-cols-3 gap-4 mt-8">
                <div class="text-center">
                    <div class="w-12 h-12 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-primary">🆓</span>
                    </div>
                    <div class="text-sm font-medium">Grátis para começar</div>
                    <div class="text-xs text-muted">Sem cartão de crédito</div>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-primary">⚡</span>
                    </div>
                    <div class="text-sm font-medium">Configuração rápida</div>
                    <div class="text-xs text-muted">Pronto em segundos</div>
                </div>
                
                <div class="text-center">
                    <div class="w-12 h-12 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-2">
                        <span class="text-primary">🔒</span>
                    </div>
                    <div class="text-sm font-medium">100% seguro</div>
                    <div class="text-xs text-muted">Dados protegidos</div>
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
    
    <!-- Script específico para registro -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const usernameField = document.getElementById('username');
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');
            
            // Validação de nome de usuário em tempo real
            usernameField.addEventListener('input', function() {
                const value = this.value;
                const regex = /^[a-zA-Z0-9_]+$/;
                
                if (value && (!regex.test(value) || value.length < 3)) {
                    this.classList.add('error');
                    CodiceCriador.showFieldError(this, 'Nome de usuário deve ter pelo menos 3 caracteres e conter apenas letras, números e underscore.');
                } else {
                    this.classList.remove('error');
                    CodiceCriador.clearFieldError(this);
                }
            });
            
            // Validação de confirmação de senha
            function validatePasswordMatch() {
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    confirmPasswordField.classList.add('error');
                    CodiceCriador.showFieldError(confirmPasswordField, 'As senhas não coincidem.');
                } else {
                    confirmPasswordField.classList.remove('error');
                    CodiceCriador.clearFieldError(confirmPasswordField);
                }
            }
            
            passwordField.addEventListener('input', validatePasswordMatch);
            confirmPasswordField.addEventListener('input', validatePasswordMatch);
            
            // Indicador de força da senha
            passwordField.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let feedback = [];
                
                if (password.length >= 6) strength++;
                else feedback.push('pelo menos 6 caracteres');
                
                if (/[a-z]/.test(password)) strength++;
                else feedback.push('letras minúsculas');
                
                if (/[A-Z]/.test(password)) strength++;
                else feedback.push('letras maiúsculas');
                
                if (/[0-9]/.test(password)) strength++;
                else feedback.push('números');
                
                if (/[^a-zA-Z0-9]/.test(password)) strength++;
                else feedback.push('símbolos');
                
                // Remover indicador anterior
                const existingIndicator = this.parentElement.querySelector('.password-strength');
                if (existingIndicator) {
                    existingIndicator.remove();
                }
                
                if (password) {
                    const indicator = document.createElement('div');
                    indicator.className = 'password-strength mt-2';
                    
                    let strengthText = '';
                    let strengthClass = '';
                    
                    if (strength <= 2) {
                        strengthText = 'Fraca';
                        strengthClass = 'text-error';
                    } else if (strength <= 3) {
                        strengthText = 'Média';
                        strengthClass = 'text-warning';
                    } else {
                        strengthText = 'Forte';
                        strengthClass = 'text-success';
                    }
                    
                    indicator.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="text-xs ${strengthClass}">Força: ${strengthText}</span>
                            <div class="flex gap-1">
                                ${Array.from({length: 5}, (_, i) => 
                                    `<div class="w-2 h-1 rounded ${i < strength ? strengthClass.replace('text-', 'bg-') : 'bg-gray-200'}"></div>`
                                ).join('')}
                            </div>
                        </div>
                    `;
                    
                    this.parentElement.appendChild(indicator);
                }
            });
            
            // Validação final do formulário
            form.addEventListener('submit', function(e) {
                const username = usernameField.value.trim();
                const email = emailField.value.trim();
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                const terms = document.querySelector('input[name="terms"]').checked;
                
                let hasErrors = false;
                
                // Validar nome de usuário
                if (!username || username.length < 3 || !/^[a-zA-Z0-9_]+$/.test(username)) {
                    CodiceCriador.showFieldError(usernameField, 'Nome de usuário inválido.');
                    hasErrors = true;
                }
                
                // Validar email
                if (!email || !email.includes('@') || !email.includes('.')) {
                    CodiceCriador.showFieldError(emailField, 'E-mail inválido.');
                    hasErrors = true;
                }
                
                // Validar senha
                if (!password || password.length < 6) {
                    CodiceCriador.showFieldError(passwordField, 'Senha deve ter pelo menos 6 caracteres.');
                    hasErrors = true;
                }
                
                // Validar confirmação
                if (password !== confirmPassword) {
                    CodiceCriador.showFieldError(confirmPasswordField, 'As senhas não coincidem.');
                    hasErrors = true;
                }
                
                // Validar termos
                if (!terms) {
                    CodiceCriador.showNotification('Você deve aceitar os termos de uso.', 'error');
                    hasErrors = true;
                }
                
                if (hasErrors) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>

