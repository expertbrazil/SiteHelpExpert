<?php
/**
 * Script de atalho para executar migrações
 * 
 * Este script é um atalho para executar o sistema de migrações
 * a partir da raiz do projeto.
 */

// Inicializar o banco de dados primeiro
require_once __DIR__ . '/database/init_database.php';

// Incluir o script principal de migrações
require_once __DIR__ . '/database/migrate.php';
?>
