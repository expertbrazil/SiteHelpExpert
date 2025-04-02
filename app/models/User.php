<?php
/**
 * Modelo de Usuário
 * 
 * Gerencia operações relacionadas aos usuários do sistema
 */
class User {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Autenticar usuário
     * 
     * @param string $username Nome de usuário
     * @param string $password Senha
     * @return array|bool Dados do usuário ou false se falhar
     */
    public function authenticate($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Atualizar último login
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Atualizar data do último login
     * 
     * @param int $userId ID do usuário
     * @return bool
     */
    private function updateLastLogin($userId) {
        $stmt = $this->conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }
    
    /**
     * Obter usuário por ID
     * 
     * @param int $id ID do usuário
     * @return array|bool Dados do usuário ou false se não encontrado
     */
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter todos os usuários
     * 
     * @return array Lista de usuários
     */
    public function getAll() {
        $stmt = $this->conn->query("SELECT id, username, name, email, role, last_login, created_at FROM users ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar novo usuário
     * 
     * @param array $data Dados do usuário
     * @return int|bool ID do usuário criado ou false se falhar
     */
    public function create($data) {
        $now = date('Y-m-d H:i:s');
        
        $stmt = $this->conn->prepare("
            INSERT INTO users (username, password, name, email, role, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['name'],
            $data['email'],
            $data['role'] ?? 'admin',
            $now,
            $now
        ]);
        
        return $success ? $this->conn->lastInsertId() : false;
    }
    
    /**
     * Atualizar usuário
     * 
     * @param int $id ID do usuário
     * @param array $data Dados do usuário
     * @return bool
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key === 'password' && !empty($value)) {
                $fields[] = "$key = ?";
                $values[] = password_hash($value, PASSWORD_DEFAULT);
            } elseif ($key !== 'password') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $fields[] = "updated_at = ?";
        $values[] = date('Y-m-d H:i:s');
        $values[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute($values);
    }
    
    /**
     * Excluir usuário
     * 
     * @param int $id ID do usuário
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Verificar se o nome de usuário já existe
     * 
     * @param string $username Nome de usuário
     * @param int $excludeId ID do usuário a ser excluído da verificação (opcional)
     * @return bool
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Verificar se o email já existe
     * 
     * @param string $email Email
     * @param int $excludeId ID do usuário a ser excluído da verificação (opcional)
     * @return bool
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() > 0;
    }
}
?>
