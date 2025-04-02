<?php
/**
 * Logout do Painel Administrativo
 */

// Carregar o bootstrap da aplicação
require_once '../../app/bootstrap.php';

// Fazer logout
auth_logout();

// Redirecionar para a página de login
header('Location: login.php');
exit;
?>
