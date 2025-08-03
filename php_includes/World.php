<?php
/**
 * Códice do Criador - Classe World
 * 
 * Classe responsável pelo gerenciamento de mundos, incluindo criação,
 * edição, colaboradores e artigos.
 * 
 * @author Manus AI
 * @version 1.0
 */

class World {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Cria um novo mundo
     * 
     * @param int $userId
     * @param string $name
     * @param string $description
     * @param string $coverImage
     * @return array
     */
    public function create($userId, $name, $description = '', $coverImage = '') {
        try {
            // Verificar se o usuário pode criar mais mundos
            $user = new User();
            if (!$user->canCreateWorld($userId)) {
                return [
                    'success' => false,
                    'message' => 'Você atingiu o limite máximo de mundos (' . MAX_WORLDS_PER_USER . ').'
                ];
            }
            
            // Validar dados
            if (empty($name) || strlen($name) < 3) {
                return [
                    'success' => false,
                    'message' => 'O nome do mundo deve ter pelo menos 3 caracteres.'
                ];
            }
            
            // Verificar se já existe um mundo com o mesmo nome para este usuário
            if ($this->db->exists('worlds', ['user_id' => $userId, 'name' => $name])) {
                return [
                    'success' => false,
                    'message' => 'Você já possui um mundo com este nome.'
                ];
            }
            
            $this->db->beginTransaction();
            
            // Inserir mundo
            $worldId = $this->db->insert('worlds', [
                'user_id' => $userId,
                'name' => $name,
                'description' => $description,
                'cover_image' => $coverImage,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Criar categorias padrão
            $this->createDefaultCategories($worldId);
            
            // Incrementar contador de mundos do usuário
            $user->incrementWorldCount($userId);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Mundo criado com sucesso!',
                'world_id' => $worldId
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logError("Erro ao criar mundo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Obtém dados de um mundo
     * 
     * @param int $worldId
     * @return array|false
     */
    public function getById($worldId) {
        return $this->db->selectOne(
            "SELECT * FROM worlds WHERE id = ?",
            [$worldId]
        );
    }
    
    /**
     * Obtém mundos de um usuário
     * 
     * @param int $userId
     * @return array
     */
    public function getByUserId($userId) {
        return $this->db->select(
            "SELECT w.*, 
                    (SELECT COUNT(*) FROM articles WHERE world_id = w.id) as article_count,
                    (SELECT COUNT(*) FROM collaborators WHERE world_id = w.id AND is_active = 1) as collaborator_count
             FROM worlds w 
             WHERE w.user_id = ? 
             ORDER BY w.updated_at DESC",
            [$userId]
        );
    }
    
    /**
     * Atualiza um mundo
     * 
     * @param int $worldId
     * @param array $data
     * @return array
     */
    public function update($worldId, $data) {
        try {
            $allowedFields = ['name', 'description', 'cover_image', 'is_public'];
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
            
            // Validar nome se estiver sendo alterado
            if (isset($updateData['name'])) {
                if (empty($updateData['name']) || strlen($updateData['name']) < 3) {
                    return [
                        'success' => false,
                        'message' => 'O nome do mundo deve ter pelo menos 3 caracteres.'
                    ];
                }
                
                // Verificar se já existe outro mundo com o mesmo nome
                $world = $this->getById($worldId);
                if ($world && $updateData['name'] !== $world['name']) {
                    if ($this->db->exists('worlds', ['user_id' => $world['user_id'], 'name' => $updateData['name']])) {
                        return [
                            'success' => false,
                            'message' => 'Você já possui um mundo com este nome.'
                        ];
                    }
                }
            }
            
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            $this->db->update('worlds', $updateData, ['id' => $worldId]);
            
            return [
                'success' => true,
                'message' => 'Mundo atualizado com sucesso!'
            ];
            
        } catch (Exception $e) {
            logError("Erro ao atualizar mundo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Deleta um mundo
     * 
     * @param int $worldId
     * @param int $userId
     * @return array
     */
    public function delete($worldId, $userId) {
        try {
            // Verificar se o mundo pertence ao usuário
            $world = $this->getById($worldId);
            if (!$world || $world['user_id'] != $userId) {
                return [
                    'success' => false,
                    'message' => 'Mundo não encontrado ou você não tem permissão.'
                ];
            }
            
            $this->db->beginTransaction();
            
            // Deletar mundo (CASCADE irá deletar artigos, categorias, etc.)
            $this->db->delete('worlds', ['id' => $worldId]);
            
            // Decrementar contador de mundos do usuário
            $this->db->query(
                "UPDATE users SET world_count = world_count - 1 WHERE id = ?",
                [$userId]
            );
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Mundo deletado com sucesso!'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logError("Erro ao deletar mundo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Obtém categorias de um mundo
     * 
     * @param int $worldId
     * @return array
     */
    public function getCategories($worldId) {
        return $this->db->select(
            "SELECT ac.*, 
                    (SELECT COUNT(*) FROM articles WHERE category_id = ac.id) as article_count
             FROM article_categories ac 
             WHERE ac.world_id = ? 
             ORDER BY ac.is_default DESC, ac.name ASC",
            [$worldId]
        );
    }
    
    /**
     * Cria uma nova categoria
     * 
     * @param int $worldId
     * @param string $name
     * @param string $description
     * @param string $icon
     * @param string $color
     * @return array
     */
    public function createCategory($worldId, $name, $description = '', $icon = '', $color = '#3b82f6') {
        try {
            if (empty($name) || strlen($name) < 2) {
                return [
                    'success' => false,
                    'message' => 'O nome da categoria deve ter pelo menos 2 caracteres.'
                ];
            }
            
            // Verificar se já existe uma categoria com o mesmo nome
            if ($this->db->exists('article_categories', ['world_id' => $worldId, 'name' => $name])) {
                return [
                    'success' => false,
                    'message' => 'Já existe uma categoria com este nome neste mundo.'
                ];
            }
            
            $categoryId = $this->db->insert('article_categories', [
                'world_id' => $worldId,
                'name' => $name,
                'description' => $description,
                'icon' => $icon,
                'color' => $color,
                'is_default' => false,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return [
                'success' => true,
                'message' => 'Categoria criada com sucesso!',
                'category_id' => $categoryId
            ];
            
        } catch (Exception $e) {
            logError("Erro ao criar categoria: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Obtém artigos de um mundo
     * 
     * @param int $worldId
     * @param int $categoryId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getArticles($worldId, $categoryId = null, $page = 1, $perPage = 20) {
        $sql = "SELECT a.*, ac.name as category_name, ac.color as category_color
                FROM articles a 
                JOIN article_categories ac ON a.category_id = ac.id 
                WHERE a.world_id = ?";
        
        $params = [$worldId];
        
        if ($categoryId) {
            $sql .= " AND a.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY a.updated_at DESC";
        
        return $this->db->paginate($sql, $params, $page, $perPage);
    }
    
    /**
     * Busca artigos em um mundo
     * 
     * @param int $worldId
     * @param string $query
     * @return array
     */
    public function searchArticles($worldId, $query) {
        return $this->db->select(
            "SELECT a.*, ac.name as category_name, ac.color as category_color
             FROM articles a 
             JOIN article_categories ac ON a.category_id = ac.id 
             WHERE a.world_id = ? AND (
                 a.title LIKE ? OR 
                 a.content_public LIKE ? OR
                 ac.name LIKE ?
             )
             ORDER BY a.updated_at DESC
             LIMIT 50",
            [$worldId, "%$query%", "%$query%", "%$query%"]
        );
    }
    
    /**
     * Obtém colaboradores de um mundo
     * 
     * @param int $worldId
     * @return array
     */
    public function getCollaborators($worldId) {
        return $this->db->select(
            "SELECT c.*, u.username, u.email
             FROM collaborators c 
             JOIN users u ON c.user_id = u.id 
             WHERE c.world_id = ? AND c.is_active = 1
             ORDER BY c.permission_level ASC, c.invited_at ASC",
            [$worldId]
        );
    }
    
    /**
     * Convida um colaborador
     * 
     * @param int $worldId
     * @param string $email
     * @param string $permission
     * @param int $invitedBy
     * @return array
     */
    public function inviteCollaborator($worldId, $email, $permission, $invitedBy) {
        try {
            // Validar permissão
            if (!in_array($permission, ['editor', 'reader'])) {
                return [
                    'success' => false,
                    'message' => 'Nível de permissão inválido.'
                ];
            }
            
            // Buscar usuário pelo email
            $user = $this->db->selectOne("SELECT id FROM users WHERE email = ?", [$email]);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Usuário não encontrado com este e-mail.'
                ];
            }
            
            // Verificar se já é colaborador
            if ($this->db->exists('collaborators', ['world_id' => $worldId, 'user_id' => $user['id']])) {
                return [
                    'success' => false,
                    'message' => 'Este usuário já é colaborador deste mundo.'
                ];
            }
            
            // Verificar se não é o próprio dono
            $world = $this->getById($worldId);
            if ($world['user_id'] == $user['id']) {
                return [
                    'success' => false,
                    'message' => 'Você não pode convidar a si mesmo.'
                ];
            }
            
            $this->db->insert('collaborators', [
                'world_id' => $worldId,
                'user_id' => $user['id'],
                'permission_level' => $permission,
                'invited_by' => $invitedBy,
                'invited_at' => date('Y-m-d H:i:s'),
                'accepted_at' => date('Y-m-d H:i:s'), // Auto-aceitar por simplicidade
                'is_active' => true
            ]);
            
            return [
                'success' => true,
                'message' => 'Colaborador convidado com sucesso!'
            ];
            
        } catch (Exception $e) {
            logError("Erro ao convidar colaborador: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Remove um colaborador
     * 
     * @param int $worldId
     * @param int $userId
     * @return array
     */
    public function removeCollaborator($worldId, $userId) {
        try {
            $this->db->update(
                'collaborators',
                ['is_active' => false],
                ['world_id' => $worldId, 'user_id' => $userId]
            );
            
            return [
                'success' => true,
                'message' => 'Colaborador removido com sucesso!'
            ];
            
        } catch (Exception $e) {
            logError("Erro ao remover colaborador: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Cria categorias padrão para um mundo
     * 
     * @param int $worldId
     */
    private function createDefaultCategories($worldId) {
        $defaultCategories = [
            ['name' => 'Personagens', 'icon' => '👤', 'color' => '#3b82f6'],
            ['name' => 'Locais', 'icon' => '🏛️', 'color' => '#10b981'],
            ['name' => 'Itens', 'icon' => '⚔️', 'color' => '#f59e0b'],
            ['name' => 'Criaturas', 'icon' => '🐉', 'color' => '#ef4444'],
            ['name' => 'História', 'icon' => '📜', 'color' => '#8b5cf6'],
            ['name' => 'Facções', 'icon' => '🏴', 'color' => '#6b7280'],
            ['name' => 'Eventos', 'icon' => '⚡', 'color' => '#f97316'],
            ['name' => 'Conceitos', 'icon' => '💡', 'color' => '#06b6d4']
        ];
        
        foreach ($defaultCategories as $category) {
            $this->db->insert('article_categories', [
                'world_id' => $worldId,
                'name' => $category['name'],
                'description' => 'Categoria padrão para ' . strtolower($category['name']),
                'icon' => $category['icon'],
                'color' => $category['color'],
                'is_default' => true,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
}
?>

