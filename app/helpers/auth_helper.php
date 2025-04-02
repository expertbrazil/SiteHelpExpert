<?php
/**
 * Helper de Autenticação
 * 
 * Funções auxiliares para gerenciar autenticação e sessões
 */

/**
 * Iniciar a sessão se ainda não foi iniciada
 */
function ensure_session_started() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Definir usuário como autenticado
 * 
 * @param array $user Dados do usuário
 */
function auth_login($user) {
    ensure_session_started();
    
    // Remover a senha dos dados da sessão
    unset($user['password']);
    
    $_SESSION['user'] = $user;
    $_SESSION['is_logged_in'] = true;
    $_SESSION['login_time'] = time();
}

/**
 * Verificar se o usuário está autenticado
 * 
 * @return bool
 */
function is_authenticated() {
    ensure_session_started();
    return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

/**
 * Obter dados do usuário autenticado
 * 
 * @return array|null Dados do usuário ou null se não estiver autenticado
 */
function get_authenticated_user() {
    ensure_session_started();
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

/**
 * Encerrar a sessão do usuário
 */
function auth_logout() {
    ensure_session_started();
    
    // Limpar todas as variáveis de sessão
    $_SESSION = [];
    
    // Destruir o cookie da sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destruir a sessão
    session_destroy();
}

/**
 * Verificar se o usuário tem permissão para acessar uma página
 * 
 * @param string|array $roles Papéis permitidos
 * @return bool
 */
function has_permission($roles = null) {
    if (!is_authenticated()) {
        return false;
    }
    
    // Se não especificar papéis, qualquer usuário autenticado tem permissão
    if ($roles === null) {
        return true;
    }
    
    $user = get_authenticated_user();
    
    if (is_array($roles)) {
        return in_array($user['role'], $roles);
    } else {
        return $user['role'] === $roles;
    }
}

/**
 * Redirecionar para a página de login se o usuário não estiver autenticado
 * 
 * @param string $redirect URL para redirecionamento após o login
 */
function require_authentication($redirect = null) {
    if (!is_authenticated()) {
        $loginUrl = '/admin/login.php';
        
        if ($redirect) {
            $loginUrl .= '?redirect=' . urlencode($redirect);
        }
        
        header('Location: ' . $loginUrl);
        exit;
    }
}

/**
 * Redirecionar para a página inicial se o usuário já estiver autenticado
 * 
 * @param string $redirect URL para redirecionamento
 */
function redirect_if_authenticated($redirect = '/admin/index.php') {
    if (is_authenticated()) {
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Gerar token CSRF para proteger formulários
 * 
 * @return string Token CSRF
 */
function generate_csrf_token() {
    ensure_session_started();
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verificar se o token CSRF é válido
 * 
 * @param string $token Token CSRF a ser verificado
 * @return bool
 */
function verify_csrf_token($token) {
    ensure_session_started();
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
