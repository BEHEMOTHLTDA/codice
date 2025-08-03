<?php
/**
 * Códice do Criador - Classe Article
 * 
 * Classe responsável pelo gerenciamento de artigos, incluindo criação,
 * edição, sistema de interconexão wiki-style e campos personalizados.
 * 
 * @author Manus AI
 * @version 1.0
 */

class Article {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Cria um novo artigo
     * 
     * @param array $data
     * @return array
     */
    public function create($data) {
        try {
            // Validar dados obrigatórios
            $validation = $this->validateArticleData($data);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Verificar permissões do mundo
            if (!hasWorldPermission($data['world_id'], 'write')) {
                return [
                    'success' => false,
                    'message' => 'Você não tem permissão para criar artigos neste mundo.'
                ];
            }
            
            // Gerar slug único
            $slug = $this->generateUniqueSlug($data['world_id'], $data['title']);
            
            $this->db->beginTransaction();
            
            // Inserir artigo
            $articleId = $this->db->insert('articles', [
                'world_id' => $data['world_id'],
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'slug' => $slug,
                'content_public' => $data['content_public'] ?? '',
                'content_private' => $data['content_private'] ?? '',
                'is_published' => $data['is_published'] ?? false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Inserir campos personalizados se existirem
            if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
                $this->saveCustomFields($articleId, $data['custom_fields']);
            }
            
            // Processar links wiki
            $this->processWikiLinks($articleId, $data['content_public'] ?? '');
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Artigo criado com sucesso!',
                'article_id' => $articleId,
                'slug' => $slug
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logError("Erro ao criar artigo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Obtém um artigo por ID
     * 
     * @param int $articleId
     * @return array|false
     */
    public function getById($articleId) {
        $article = $this->db->selectOne(
            "SELECT a.*, ac.name as category_name, ac.color as category_color, w.name as world_name
             FROM articles a 
             JOIN article_categories ac ON a.category_id = ac.id 
             JOIN worlds w ON a.world_id = w.id 
             WHERE a.id = ?",
            [$articleId]
        );
        
        if ($article) {
            // Carregar campos personalizados
            $article['custom_fields'] = $this->getCustomFields($articleId);
            
            // Carregar links referenciados
            $article['referenced_links'] = $this->getReferencedLinks($articleId);
            
            // Carregar links que referenciam este artigo
            $article['backlinks'] = $this->getBacklinks($articleId);
        }
        
        return $article;
    }
    
    /**
     * Obtém um artigo por slug
     * 
     * @param int $worldId
     * @param string $slug
     * @return array|false
     */
    public function getBySlug($worldId, $slug) {
        $article = $this->db->selectOne(
            "SELECT a.*, ac.name as category_name, ac.color as category_color, w.name as world_name
             FROM articles a 
             JOIN article_categories ac ON a.category_id = ac.id 
             JOIN worlds w ON a.world_id = w.id 
             WHERE a.world_id = ? AND a.slug = ?",
            [$worldId, $slug]
        );
        
        if ($article) {
            // Incrementar contador de visualizações
            $this->incrementViewCount($article['id']);
            
            // Carregar campos personalizados
            $article['custom_fields'] = $this->getCustomFields($article['id']);
            
            // Carregar links
            $article['referenced_links'] = $this->getReferencedLinks($article['id']);
            $article['backlinks'] = $this->getBacklinks($article['id']);
        }
        
        return $article;
    }
    
    /**
     * Atualiza um artigo
     * 
     * @param int $articleId
     * @param array $data
     * @return array
     */
    public function update($articleId, $data) {
        try {
            // Verificar se o artigo existe
            $article = $this->getById($articleId);
            if (!$article) {
                return [
                    'success' => false,
                    'message' => 'Artigo não encontrado.'
                ];
            }
            
            // Verificar permissões
            if (!hasWorldPermission($article['world_id'], 'write')) {
                return [
                    'success' => false,
                    'message' => 'Você não tem permissão para editar este artigo.'
                ];
            }
            
            // Validar dados
            $validation = $this->validateArticleData($data, $articleId);
            if (!$validation['success']) {
                return $validation;
            }
            
            $this->db->beginTransaction();
            
            // Preparar dados para atualização
            $updateData = [];
            $allowedFields = ['title', 'category_id', 'content_public', 'content_private', 'is_published'];
            
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateData[$field] = $data[$field];
                }
            }
            
            // Gerar novo slug se o título mudou
            if (isset($data['title']) && $data['title'] !== $article['title']) {
                $updateData['slug'] = $this->generateUniqueSlug($article['world_id'], $data['title'], $articleId);
            }
            
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            // Atualizar artigo
            $this->db->update('articles', $updateData, ['id' => $articleId]);
            
            // Atualizar campos personalizados
            if (isset($data['custom_fields']) && is_array($data['custom_fields'])) {
                $this->saveCustomFields($articleId, $data['custom_fields']);
            }
            
            // Reprocessar links wiki se o conteúdo mudou
            if (isset($data['content_public'])) {
                $this->processWikiLinks($articleId, $data['content_public']);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Artigo atualizado com sucesso!'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logError("Erro ao atualizar artigo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Deleta um artigo
     * 
     * @param int $articleId
     * @return array
     */
    public function delete($articleId) {
        try {
            $article = $this->getById($articleId);
            if (!$article) {
                return [
                    'success' => false,
                    'message' => 'Artigo não encontrado.'
                ];
            }
            
            // Verificar permissões
            if (!hasWorldPermission($article['world_id'], 'write')) {
                return [
                    'success' => false,
                    'message' => 'Você não tem permissão para deletar este artigo.'
                ];
            }
            
            $this->db->beginTransaction();
            
            // Deletar artigo (CASCADE irá deletar campos personalizados e outros relacionados)
            $this->db->delete('articles', ['id' => $articleId]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Artigo deletado com sucesso!'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logError("Erro ao deletar artigo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno. Tente novamente mais tarde.'
            ];
        }
    }
    
    /**
     * Busca artigos
     * 
     * @param int $worldId
     * @param string $query
     * @param int $categoryId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function search($worldId, $query = '', $categoryId = null, $page = 1, $perPage = 20) {
        $sql = "SELECT a.*, ac.name as category_name, ac.color as category_color
                FROM articles a 
                JOIN article_categories ac ON a.category_id = ac.id 
                WHERE a.world_id = ?";
        
        $params = [$worldId];
        
        if (!empty($query)) {
            $sql .= " AND (a.title LIKE ? OR a.content_public LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }
        
        if ($categoryId) {
            $sql .= " AND a.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY a.updated_at DESC";
        
        return $this->db->paginate($sql, $params, $page, $perPage);
    }
    
    /**
     * Obtém artigos de uma categoria
     * 
     * @param int $categoryId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getByCategory($categoryId, $page = 1, $perPage = 20) {
        $sql = "SELECT a.*, ac.name as category_name, ac.color as category_color
                FROM articles a 
                JOIN article_categories ac ON a.category_id = ac.id 
                WHERE a.category_id = ?
                ORDER BY a.title ASC";
        
        return $this->db->paginate($sql, [$categoryId], $page, $perPage);
    }
    
    /**
     * Obtém campos personalizados de um artigo
     * 
     * @param int $articleId
     * @return array
     */
    public function getCustomFields($articleId) {
        return $this->db->select(
            "SELECT afv.*, acf.field_name, acf.field_type, acf.field_options
             FROM article_field_values afv
             JOIN article_custom_fields acf ON afv.field_id = acf.id
             WHERE afv.article_id = ?
             ORDER BY acf.field_order ASC",
            [$articleId]
        );
    }
    
    /**
     * Salva campos personalizados
     * 
     * @param int $articleId
     * @param array $customFields
     */
    private function saveCustomFields($articleId, $customFields) {
        // Deletar valores existentes
        $this->db->delete('article_field_values', ['article_id' => $articleId]);
        
        // Inserir novos valores
        foreach ($customFields as $fieldId => $value) {
            if (!empty($value)) {
                $this->db->insert('article_field_values', [
                    'article_id' => $articleId,
                    'field_id' => $fieldId,
                    'field_value' => $value
                ]);
            }
        }
    }
    
    /**
     * Processa links wiki no conteúdo
     * 
     * @param int $articleId
     * @param string $content
     */
    private function processWikiLinks($articleId, $content) {
        // Padrão para encontrar links wiki: @[Nome do Artigo]
        preg_match_all('/@\[([^\]]+)\]/', $content, $matches);
        
        if (!empty($matches[1])) {
            // Salvar links referenciados (implementar se necessário)
            // Por simplicidade, vamos apenas processar os links no momento da exibição
        }
    }
    
    /**
     * Obtém links referenciados por um artigo
     * 
     * @param int $articleId
     * @return array
     */
    public function getReferencedLinks($articleId) {
        $article = $this->db->selectOne("SELECT content_public FROM articles WHERE id = ?", [$articleId]);
        
        if (!$article) return [];
        
        preg_match_all('/@\[([^\]]+)\]/', $article['content_public'], $matches);
        
        $links = [];
        if (!empty($matches[1])) {
            $uniqueLinks = array_unique($matches[1]);
            
            foreach ($uniqueLinks as $linkTitle) {
                // Buscar se o artigo existe
                $linkedArticle = $this->db->selectOne(
                    "SELECT id, title, slug FROM articles WHERE title = ? AND world_id = (SELECT world_id FROM articles WHERE id = ?)",
                    [$linkTitle, $articleId]
                );
                
                $links[] = [
                    'title' => $linkTitle,
                    'exists' => $linkedArticle !== false,
                    'article_id' => $linkedArticle['id'] ?? null,
                    'slug' => $linkedArticle['slug'] ?? null
                ];
            }
        }
        
        return $links;
    }
    
    /**
     * Obtém backlinks (artigos que referenciam este artigo)
     * 
     * @param int $articleId
     * @return array
     */
    public function getBacklinks($articleId) {
        $article = $this->db->selectOne("SELECT title, world_id FROM articles WHERE id = ?", [$articleId]);
        
        if (!$article) return [];
        
        // Buscar artigos que contenham referência a este artigo
        return $this->db->select(
            "SELECT id, title, slug 
             FROM articles 
             WHERE world_id = ? AND content_public LIKE ? AND id != ?",
            [$article['world_id'], '%@[' . $article['title'] . ']%', $articleId]
        );
    }
    
    /**
     * Incrementa contador de visualizações
     * 
     * @param int $articleId
     */
    private function incrementViewCount($articleId) {
        $this->db->query(
            "UPDATE articles SET view_count = view_count + 1 WHERE id = ?",
            [$articleId]
        );
    }
    
    /**
     * Gera slug único para o artigo
     * 
     * @param int $worldId
     * @param string $title
     * @param int $excludeId
     * @return string
     */
    private function generateUniqueSlug($worldId, $title, $excludeId = null) {
        $baseSlug = $this->slugify($title);
        $slug = $baseSlug;
        $counter = 1;
        
        while (true) {
            $sql = "SELECT id FROM articles WHERE world_id = ? AND slug = ?";
            $params = [$worldId, $slug];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $existing = $this->db->selectOne($sql, $params);
            
            if (!$existing) {
                break;
            }
            
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Converte texto em slug
     * 
     * @param string $text
     * @return string
     */
    private function slugify($text) {
        // Converter para minúsculas
        $text = strtolower($text);
        
        // Remover acentos
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        
        // Remover caracteres especiais
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        
        // Substituir espaços e múltiplos hífens por um hífen
        $text = preg_replace('/[\s-]+/', '-', $text);
        
        // Remover hífens do início e fim
        $text = trim($text, '-');
        
        return $text;
    }
    
    /**
     * Valida dados do artigo
     * 
     * @param array $data
     * @param int $excludeId
     * @return array
     */
    private function validateArticleData($data, $excludeId = null) {
        if (empty($data['title'])) {
            return [
                'success' => false,
                'message' => 'O título é obrigatório.'
            ];
        }
        
        if (strlen($data['title']) < 3) {
            return [
                'success' => false,
                'message' => 'O título deve ter pelo menos 3 caracteres.'
            ];
        }
        
        if (strlen($data['title']) > 200) {
            return [
                'success' => false,
                'message' => 'O título não pode ter mais de 200 caracteres.'
            ];
        }
        
        if (empty($data['world_id'])) {
            return [
                'success' => false,
                'message' => 'O mundo é obrigatório.'
            ];
        }
        
        if (empty($data['category_id'])) {
            return [
                'success' => false,
                'message' => 'A categoria é obrigatória.'
            ];
        }
        
        // Verificar se já existe um artigo com o mesmo título no mundo
        $sql = "SELECT id FROM articles WHERE world_id = ? AND title = ?";
        $params = [$data['world_id'], $data['title']];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $existing = $this->db->selectOne($sql, $params);
        
        if ($existing) {
            return [
                'success' => false,
                'message' => 'Já existe um artigo com este título neste mundo.'
            ];
        }
        
        return ['success' => true];
    }
    
    /**
     * Converte conteúdo com links wiki para HTML
     * 
     * @param string $content
     * @param int $worldId
     * @return string
     */
    public function processContentForDisplay($content, $worldId) {
        // Converter markdown básico
        $html = $this->markdownToHtml($content);
        
        // Processar links wiki
        $html = preg_replace_callback('/@\[([^\]]+)\]/', function($matches) use ($worldId) {
            $articleTitle = $matches[1];
            
            // Buscar artigo
            $article = $this->db->selectOne(
                "SELECT id, slug FROM articles WHERE title = ? AND world_id = ?",
                [$articleTitle, $worldId]
            );
            
            if ($article) {
                return '<a href="article-view.php?id=' . $article['id'] . '" class="wiki-link wiki-link-exists" title="' . htmlspecialchars($articleTitle) . '">' . htmlspecialchars($articleTitle) . '</a>';
            } else {
                return '<a href="article-create.php?world_id=' . $worldId . '&title=' . urlencode($articleTitle) . '" class="wiki-link wiki-link-missing" title="Criar artigo: ' . htmlspecialchars($articleTitle) . '">' . htmlspecialchars($articleTitle) . '</a>';
            }
        }, $html);
        
        return $html;
    }
    
    /**
     * Converte markdown básico para HTML
     * 
     * @param string $content
     * @return string
     */
    private function markdownToHtml($content) {
        // Escapar HTML
        $content = htmlspecialchars($content);
        
        // Converter markdown
        $content = preg_replace('/### (.*)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/## (.*)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/# (.*)/m', '<h1>$1</h1>', $content);
        $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $content);
        $content = preg_replace('/`(.*?)`/s', '<code>$1</code>', $content);
        
        // Converter quebras de linha
        $content = preg_replace('/\n\n+/', '</p><p>', $content);
        $content = preg_replace('/\n/', '<br>', $content);
        
        // Envolver em parágrafos
        $content = '<p>' . $content . '</p>';
        
        // Limpar parágrafos vazios
        $content = preg_replace('/<p><\/p>/', '', $content);
        
        return $content;
    }
}
?>

