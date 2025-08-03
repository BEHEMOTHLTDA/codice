<?php
/**
 * Códice do Criador - Logout
 * 
 * Script para fazer logout do usuário e redirecionar
 * para a página inicial.
 * 
 * @author Manus AI
 * @version 1.0
 */

require_once 'php_includes/config.php';

// Fazer logout se o usuário estiver logado
if (isLoggedIn()) {
    $user = new User();
    $user->logout();
    
    setFlashMessage('success', 'Logout realizado com sucesso!');
}

// Redirecionar para a página inicial
redirect('index.php');
?>

