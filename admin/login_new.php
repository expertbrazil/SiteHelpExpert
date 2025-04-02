<?php
// Incluir o arquivo de bootstrap da aplicação
require_once '../app/bootstrap.php';

// Redirecionar para a página de login na nova estrutura
header('Location: ../public/admin/login.php');
exit;
?>
