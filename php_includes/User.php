<?php
/**
 * Códice do Criador - Classe User
 * 
 * Classe responsável pelo gerenciamento de usuários, incluindo autenticação,
 * cadastro, recuperação de senha e gerenciamento de sessões.
 * 
 * @author Manus AI
 * @version 1.0
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Registra um novo usuário
     * 
     * @param string $username
     * @param string $email
     * @param string $password
     * @return array
     */
    public function register($username, $email, $password) {
        try {
            // Validar dados de entrada
            $validation = $this->validateRegistrationData($username, $email, $password);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Verificar se o usuário já existe
            if ($this->userExists($username, $email)) {
                return [
                    'success' => false,
                    'message' => 'Nome de usuário ou e-mail já estão em uso.'
                ];
            }
            
            // Hash da senha
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Inserir usuário no banco
            $userId = $this->db->insert('users', [
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'message' => 'Usuário registrado com sucesso!',
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            logError("Erro no registro de usuário: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Autentica um usuário
     * 
     * @param string $email
     * @param string $password
     * @param bool $rememberMe
     * @return array
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Buscar usuário por email
            $user = $this->db->selectOne(
                "SELECT id, username, email, password_hash, is_active FROM users WHERE email = ?",
                [$email]
            );
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'E-mail ou senha incorretos.'
                ];
            }
            
            if (!$user['is_active']) {
                return [
                    'success' => false,
                    'message' => 'Conta desativada. Entre em contato com o suporte.'
                ];
            }
            
            // Verificar senha
            if (!password_verify($password, $user['password_hash'])) {
                return [
                    'success' => false,
                    'message' => 'E-mail ou senha incorretos.'
                ];
            }
            
            // Iniciar sessão
            $this->startSession($user['id']);
            
            // Criar token de "lembrar-me" se solicitado
            if ($rememberMe) {
                $this->createRememberToken($user['id']);
            }
            
            return [
                'success' => true,
                'message' => 'Login realizado com sucesso!',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ];
            
        } catch (Exception $e) {
            logError("Erro no login: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Faz logout do usuário
     */
    public function logout() {
        // Remover token de "lembrar-me" se existir
        if (isset($_COOKIE['remember_token'])) {
            $this->removeRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Destruir sessão
        session_destroy();
        session_start();
    }
    
    /**
     * Verifica se o usuário está autenticado via token de "lembrar-me"
     */
    public function checkRememberToken() {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        $token = $_COOKIE['remember_token'];
        
        $session = $this->db->selectOne(
            "SELECT user_id FROM user_sessions WHERE session_token = ? AND expires_at > NOW()",
            [$token]
        );
        
        if ($session) {
            $this->startSession($session['user_id']);
            return true;
        }
        
        // Token inválido ou expirado, remover cookie
        setcookie('remember_token', '', time() - 3600, '/');
        return false;
    }
    
    /**
     * Solicita recuperação de senha
     * 
     * @param string $email
     * @return array
     */
    public function requestPasswordReset($email) {
        try {
            $user = $this->db->selectOne("SELECT id FROM users WHERE email = ?", [$email]);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'E-mail não encontrado.'
                ];
            }
            
            // Gerar token único
            $token = generateSecureToken();
            $expiresAt = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);
            
            // Invalidar tokens anteriores
            $this->db->update(
                'password_reset_tokens',
                ['used' => 1],
                ['user_id' => $user['id']]
            );
            
            // Inserir novo token
            $this->db->insert('password_reset_tokens', [
                'user_id' => $user['id'],
                'token' => $token,
                'expires_at' => $expiresAt
            ]);
            
            // Aqui você enviaria o e-mail com o token
            // Por simplicidade, vamos apenas retornar o token
            
            return [
                'success' => true,
                'message' => 'E-mail de recuperação enviado!',
                'token' => $token // Remover em produção
            ];
            
        } catch (Exception $e) {
            logError("Erro na recuperação de senha: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Redefine a senha usando token
     * 
     * @param string $token
     * @param string $newPassword
     * @return array
     */
    public function resetPassword($token, $newPassword) {
        try {
            // Validar nova senha
            if (strlen($newPassword) < 6) {
                return [
                    'success' => false,
                    'message' => 'A senha deve ter pelo menos 6 caracteres.'
                ];
            }
            
            // Verificar token
            $resetToken = $this->db->selectOne(
                "SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = 0",
                [$token]
            );
            
            if (!$resetToken) {
                return [
                    'success' => false,
                    'message' => 'Token inválido ou expirado.'
                ];
            }
            
            // Atualizar senha
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->update(
                'users',
                ['password_hash' => $passwordHash],
                ['id' => $resetToken['user_id']]
            );
            
            // Marcar token como usado
            $this->db->update(
                'password_reset_tokens',
                ['used' => 1],
                ['token' => $token]
            );
            
            return [
                'success' => true,
                'message' => 'Senha redefinida com sucesso!'
            ];
            
        } catch (Exception $e) {
            logError("Erro na redefinição de senha: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Obtém dados do usuário por ID
     * 
     * @param int $userId
     * @return array|false
     */
    public function getUserById($userId) {
        return $this->db->selectOne(
            "SELECT id, username, email, world_count, story_count, rpg_system_count, theme_preference, created_at FROM users WHERE id = ?",
            [$userId]
        );
    }
    
    /**
     * Atualiza o perfil do usuário
     * 
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['username', 'email', 'theme_preference'];
            $updateData = [];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            if (empty($updateData)) {
                return [
                    'success' => false,
                    'message' => 'Nenhum dado para atualizar.'
                ];
            }
            
            // Verificar se username/email já existem (se estão sendo alterados)
            if (isset($updateData['username']) || isset($updateData['email'])) {
                $currentUser = $this->getUserById($userId);
                
                if (isset($updateData['username']) && $updateData['username'] !== $currentUser['username']) {
                    if ($this->db->exists('users', ['username' => $updateData['username']])) {
                        return [
                            'success' => false,
                            'message' => 'Nome de usuário já está em uso.'
                        ];
                    }
                }
                
                if (isset($updateData['email']) && $updateData['email'] !== $currentUser['email']) {
                    if (!isValidEmail($updateData['email'])) {
                        return [
                            'success' => false,
                            'message' => 'E-mail inválido.'
                        ];
                    }
                    
                    if ($this->db->exists('users', ['email' => $updateData['email']])) {
                        return [
                            'success' => false,
                            'message' => 'E-mail já está em uso.'
                        ];
                    }
                }
            }
            
            $this->db->update('users', $updateData, ['id' => $userId]);
            
            return [
                'success' => true,
                'message' => 'Perfil atualizado com sucesso!'
            ];
            
        } catch (Exception $e) {
            logError("Erro na atualização do perfil: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Verifica se o usuário pode criar um novo mundo
     * 
     * @param int $userId
     * @return bool
     */
    public function canCreateWorld($userId) {
        $user = $this->getUserById($userId);
        return $user && $user['world_count'] < MAX_WORLDS_PER_USER;
    }
    
    /**
     * Verifica se o usuário pode criar uma nova história
     * 
     * @param int $userId
     * @return bool
     */
    public function canCreateStory($userId) {
        $user = $this->getUserById($userId);
        return $user && $user['story_count'] < MAX_STORIES_PER_USER;
    }
    
    /**
     * Incrementa o contador de mundos do usuário
     * 
     * @param int $userId
     */
    public function incrementWorldCount($userId) {
        $this->db->query(
            "UPDATE users SET world_count = world_count + 1 WHERE id = ?",
            [$userId]
        );
    }
    
    /**
     * Incrementa o contador de histórias do usuário
     * 
     * @param int $userId
     */
    public function incrementStoryCount($userId) {
        $this->db->query(
            "UPDATE users SET story_count = story_count + 1 WHERE id = ?",
            [$userId]
        );
    }
    
    /**
     * Valida dados de registro
     * 
     * @param string $username
     * @param string $email
     * @param string $password
     * @return array
     */
    private function validateRegistrationData($username, $email, $password) {
        if (empty($username) || empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Todos os campos são obrigatórios.'
            ];
        }
        
        if (strlen($username) < 3 || strlen($username) > 50) {
            return [
                'success' => false,
                'message' => 'O nome de usuário deve ter entre 3 e 50 caracteres.'
            ];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return [
                'success' => false,
                'message' => 'O nome de usuário pode conter apenas letras, números e underscore.'
            ];
        }
        
        if (!isValidEmail($email)) {
            return [
                'success' => false,
                'message' => 'E-mail inválido.'
            ];
        }
        
        if (strlen($password) < 6) {
            return [
                'success' => false,
                'message' => 'A senha deve ter pelo menos 6 caracteres.'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Verifica se o usuário já existe
     * 
     * @param string $username
     * @param string $email
     * @return bool
     */
    private function userExists($username, $email) {
        return $this->db->exists('users', ['username' => $username]) ||
               $this->db->exists('users', ['email' => $email]);
    }
    
    /**
     * Inicia a sessão do usuário
     * 
     * @param int $userId
     */
    private function startSession($userId) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['login_time'] = time();
        session_regenerate_id(true);
    }
    
    /**
     * Cria token de "lembrar-me"
     * 
     * @param int $userId
     */
    private function createRememberToken($userId) {
        $token = generateSecureToken();
        $expiresAt = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        
        $this->db->insert('user_sessions', [
            'user_id' => $userId,
            'session_token' => $token,
            'expires_at' => $expiresAt,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        setcookie('remember_token', $token, time() + SESSION_LIFETIME, '/');
    }
    
    /**
     * Remove token de "lembrar-me"
     * 
     * @param string $token
     */
    private function removeRememberToken($token) {
        $this->db->delete('user_sessions', ['session_token' => $token]);
    }
}
?>

