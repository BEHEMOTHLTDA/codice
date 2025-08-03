<?php
/**
 * Códice do Criador - Assistente de IA
 * 
 * Classe responsável pela integração com APIs de IA para auxiliar
 * na criação de conteúdo, geração de ideias e sugestões criativas.
 * 
 * @author Manus AI
 * @version 1.0
 */

class AIAssistant {
    private $apiKey;
    private $apiBase;
    private $db;
    
    public function __construct() {
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
        $this->apiBase = $_ENV['OPENAI_API_BASE'] ?? 'https://api.openai.com/v1';
        $this->db = Database::getInstance();
    }
    
    /**
     * Gera sugestões de conteúdo para um artigo
     * 
     * @param string $title
     * @param string $category
     * @param string $worldContext
     * @param string $existingContent
     * @return array
     */
    public function generateContentSuggestions($title, $category, $worldContext = '', $existingContent = '') {
        try {
            $prompt = $this->buildContentPrompt($title, $category, $worldContext, $existingContent);
            
            $response = $this->callOpenAI([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um assistente criativo especializado em worldbuilding e criação de conteúdo para RPG e ficção. Forneça sugestões detalhadas, criativas e coerentes.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.8
            ]);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'suggestions' => $this->parseContentSuggestions($response['data']['choices'][0]['message']['content'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao gerar sugestões: ' . $response['error']
                ];
            }
            
        } catch (Exception $e) {
            logError("Erro no assistente de IA: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do assistente de IA.'
            ];
        }
    }
    
    /**
     * Gera ideias para novos artigos baseado no mundo
     * 
     * @param int $worldId
     * @param string $category
     * @return array
     */
    public function generateArticleIdeas($worldId, $category = '') {
        try {
            // Obter contexto do mundo
            $worldContext = $this->getWorldContext($worldId);
            
            $prompt = $this->buildArticleIdeasPrompt($worldContext, $category);
            
            $response = $this->callOpenAI([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um especialista em worldbuilding. Gere ideias criativas e originais para artigos que enriqueçam o mundo criado pelo usuário.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 800,
                'temperature' => 0.9
            ]);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'ideas' => $this->parseArticleIdeas($response['data']['choices'][0]['message']['content'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao gerar ideias: ' . $response['error']
                ];
            }
            
        } catch (Exception $e) {
            logError("Erro ao gerar ideias: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do assistente de IA.'
            ];
        }
    }
    
    /**
     * Expande um texto existente com mais detalhes
     * 
     * @param string $content
     * @param string $context
     * @param string $direction
     * @return array
     */
    public function expandContent($content, $context = '', $direction = 'general') {
        try {
            $prompt = $this->buildExpansionPrompt($content, $context, $direction);
            
            $response = $this->callOpenAI([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um escritor especializado em expandir e enriquecer conteúdo criativo. Mantenha consistência com o material existente.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1200,
                'temperature' => 0.7
            ]);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'expanded_content' => trim($response['data']['choices'][0]['message']['content'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao expandir conteúdo: ' . $response['error']
                ];
            }
            
        } catch (Exception $e) {
            logError("Erro ao expandir conteúdo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do assistente de IA.'
            ];
        }
    }
    
    /**
     * Gera nomes criativos
     * 
     * @param string $type (person, place, item, creature, faction)
     * @param string $context
     * @param int $count
     * @return array
     */
    public function generateNames($type, $context = '', $count = 10) {
        try {
            $prompt = $this->buildNamePrompt($type, $context, $count);
            
            $response = $this->callOpenAI([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um especialista em criar nomes únicos e memoráveis para elementos de ficção e RPG.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 500,
                'temperature' => 0.9
            ]);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'names' => $this->parseNames($response['data']['choices'][0]['message']['content'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao gerar nomes: ' . $response['error']
                ];
            }
            
        } catch (Exception $e) {
            logError("Erro ao gerar nomes: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do assistente de IA.'
            ];
        }
    }
    
    /**
     * Analisa e sugere melhorias para um texto
     * 
     * @param string $content
     * @param string $type (article, story, description)
     * @return array
     */
    public function analyzeAndImprove($content, $type = 'article') {
        try {
            $prompt = $this->buildAnalysisPrompt($content, $type);
            
            $response = $this->callOpenAI([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Você é um editor experiente especializado em ficção e worldbuilding. Forneça análises construtivas e sugestões práticas.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1000,
                'temperature' => 0.6
            ]);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'analysis' => $this->parseAnalysis($response['data']['choices'][0]['message']['content'])
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erro ao analisar conteúdo: ' . $response['error']
                ];
            }
            
        } catch (Exception $e) {
            logError("Erro ao analisar conteúdo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do assistente de IA.'
            ];
        }
    }
    
    /**
     * Chama a API da OpenAI
     * 
     * @param array $data
     * @return array
     */
    private function callOpenAI($data) {
        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Chave da API não configurada.'
            ];
        }
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiBase . '/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $error
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => 'Erro HTTP: ' . $httpCode
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Erro ao decodificar resposta JSON.'
            ];
        }
        
        if (isset($decodedResponse['error'])) {
            return [
                'success' => false,
                'error' => $decodedResponse['error']['message'] ?? 'Erro desconhecido da API.'
            ];
        }
        
        return [
            'success' => true,
            'data' => $decodedResponse
        ];
    }
    
    /**
     * Constrói prompt para sugestões de conteúdo
     */
    private function buildContentPrompt($title, $category, $worldContext, $existingContent) {
        $prompt = "Preciso de sugestões criativas para desenvolver um artigo de worldbuilding.\n\n";
        $prompt .= "**Título:** $title\n";
        $prompt .= "**Categoria:** $category\n";
        
        if ($worldContext) {
            $prompt .= "**Contexto do Mundo:** $worldContext\n";
        }
        
        if ($existingContent) {
            $prompt .= "**Conteúdo Existente:** $existingContent\n";
        }
        
        $prompt .= "\nPor favor, forneça:\n";
        $prompt .= "1. 3-5 aspectos principais para desenvolver\n";
        $prompt .= "2. Detalhes específicos para cada aspecto\n";
        $prompt .= "3. Conexões possíveis com outros elementos do mundo\n";
        $prompt .= "4. Elementos únicos que tornariam este artigo memorável\n\n";
        $prompt .= "Seja criativo, detalhado e mantenha consistência com o contexto fornecido.";
        
        return $prompt;
    }
    
    /**
     * Constrói prompt para ideias de artigos
     */
    private function buildArticleIdeasPrompt($worldContext, $category) {
        $prompt = "Baseado no seguinte contexto de mundo, gere ideias criativas para novos artigos:\n\n";
        $prompt .= "**Contexto do Mundo:** $worldContext\n";
        
        if ($category) {
            $prompt .= "**Categoria Específica:** $category\n";
        }
        
        $prompt .= "\nGere 8-10 ideias de artigos que:\n";
        $prompt .= "1. Sejam únicos e interessantes\n";
        $prompt .= "2. Se conectem bem com o mundo existente\n";
        $prompt .= "3. Ofereçam potencial para desenvolvimento rico\n";
        $prompt .= "4. Cubram diferentes aspectos do worldbuilding\n\n";
        $prompt .= "Para cada ideia, forneça:\n";
        $prompt .= "- Título sugerido\n";
        $prompt .= "- Breve descrição (1-2 frases)\n";
        $prompt .= "- Categoria recomendada\n";
        
        return $prompt;
    }
    
    /**
     * Obtém contexto do mundo para IA
     */
    private function getWorldContext($worldId) {
        $world = $this->db->selectOne("SELECT name, description FROM worlds WHERE id = ?", [$worldId]);
        
        if (!$world) return '';
        
        $context = "Mundo: " . $world['name'] . "\n";
        if ($world['description']) {
            $context .= "Descrição: " . $world['description'] . "\n";
        }
        
        // Obter alguns artigos existentes para contexto
        $articles = $this->db->select(
            "SELECT title, content_public FROM articles WHERE world_id = ? AND is_published = 1 ORDER BY updated_at DESC LIMIT 5",
            [$worldId]
        );
        
        if (!empty($articles)) {
            $context .= "\nArtigos Existentes:\n";
            foreach ($articles as $article) {
                $context .= "- " . $article['title'] . ": " . substr($article['content_public'], 0, 200) . "...\n";
            }
        }
        
        return $context;
    }
    
    /**
     * Processa sugestões de conteúdo da IA
     */
    private function parseContentSuggestions($content) {
        // Implementar parsing das sugestões
        // Por simplicidade, retornar o conteúdo bruto formatado
        return [
            'raw_content' => $content,
            'formatted_content' => nl2br(htmlspecialchars($content))
        ];
    }
    
    /**
     * Processa ideias de artigos da IA
     */
    private function parseArticleIdeas($content) {
        // Implementar parsing das ideias
        $ideas = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '-') !== 0) continue;
            
            $ideas[] = [
                'title' => $line,
                'description' => ''
            ];
        }
        
        return $ideas;
    }
    
    /**
     * Processa nomes gerados pela IA
     */
    private function parseNames($content) {
        $names = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Remover numeração e formatação
            $line = preg_replace('/^\d+\.\s*/', '', $line);
            $line = preg_replace('/^-\s*/', '', $line);
            
            if (!empty($line)) {
                $names[] = $line;
            }
        }
        
        return $names;
    }
    
    /**
     * Processa análise da IA
     */
    private function parseAnalysis($content) {
        return [
            'raw_analysis' => $content,
            'formatted_analysis' => nl2br(htmlspecialchars($content))
        ];
    }
    
    /**
     * Constrói outros prompts necessários
     */
    private function buildExpansionPrompt($content, $context, $direction) {
        $prompt = "Expanda o seguinte conteúdo com mais detalhes e profundidade:\n\n";
        $prompt .= "**Conteúdo Original:** $content\n";
        
        if ($context) {
            $prompt .= "**Contexto:** $context\n";
        }
        
        $prompt .= "**Direção da Expansão:** $direction\n\n";
        $prompt .= "Adicione detalhes ricos, mantenha consistência e torne o conteúdo mais envolvente.";
        
        return $prompt;
    }
    
    private function buildNamePrompt($type, $context, $count) {
        $prompt = "Gere $count nomes únicos e criativos para: $type\n";
        
        if ($context) {
            $prompt .= "Contexto: $context\n";
        }
        
        $prompt .= "\nOs nomes devem ser:\n";
        $prompt .= "- Únicos e memoráveis\n";
        $prompt .= "- Apropriados para o contexto\n";
        $prompt .= "- Fáceis de pronunciar\n";
        $prompt .= "- Evocativos e interessantes\n";
        
        return $prompt;
    }
    
    private function buildAnalysisPrompt($content, $type) {
        $prompt = "Analise o seguinte $type e forneça sugestões de melhoria:\n\n";
        $prompt .= "$content\n\n";
        $prompt .= "Forneça:\n";
        $prompt .= "1. Pontos fortes do texto\n";
        $prompt .= "2. Áreas que podem ser melhoradas\n";
        $prompt .= "3. Sugestões específicas de edição\n";
        $prompt .= "4. Ideias para expandir o conteúdo\n";
        
        return $prompt;
    }
}
?>

