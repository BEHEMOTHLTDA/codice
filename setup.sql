-- Códice do Criador - Script de Configuração do Banco de Dados
-- Criado por: Manus AI
-- Data: 2025-01-08

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS codice_do_criador CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE codice_do_criador;

-- Tabela de usuários
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    world_count INT DEFAULT 0,
    story_count INT DEFAULT 0,
    rpg_system_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    theme_preference ENUM('light', 'dark') DEFAULT 'light',
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- Tabela de mundos
CREATE TABLE worlds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    cover_image VARCHAR(255),
    is_public BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Tabela de histórias
CREATE TABLE stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    template_type ENUM('book', 'screenplay', 'rpg_adventure') NOT NULL,
    description TEXT,
    word_count INT DEFAULT 0,
    word_goal INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Tabela de sistemas de RPG
CREATE TABLE rpg_systems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Tabela de categorias de artigos
CREATE TABLE article_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    world_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    icon VARCHAR(50),
    color VARCHAR(7), -- Código hexadecimal da cor
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (world_id) REFERENCES worlds(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category_per_world (world_id, name),
    INDEX idx_world_id (world_id)
);

-- Tabela de artigos
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    world_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(250) NOT NULL,
    content_public LONGTEXT,
    content_private LONGTEXT,
    is_published BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (world_id) REFERENCES worlds(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES article_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_slug_per_world (world_id, slug),
    INDEX idx_world_id (world_id),
    INDEX idx_category_id (category_id),
    INDEX idx_title (title),
    FULLTEXT idx_content (title, content_public)
);

-- Tabela de colaboradores
CREATE TABLE collaborators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    world_id INT NOT NULL,
    user_id INT NOT NULL,
    permission_level ENUM('owner', 'editor', 'reader') NOT NULL,
    invited_by INT NOT NULL,
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (world_id) REFERENCES worlds(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_collaborator_per_world (world_id, user_id),
    INDEX idx_world_id (world_id),
    INDEX idx_user_id (user_id)
);

-- Tabela de imagens/mídia
CREATE TABLE media_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    world_id INT,
    article_id INT,
    story_id INT,
    rpg_system_id INT,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    alt_text VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (world_id) REFERENCES worlds(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (rpg_system_id) REFERENCES rpg_systems(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_world_id (world_id),
    INDEX idx_article_id (article_id)
);

-- Tabela de capítulos de histórias
CREATE TABLE story_chapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    story_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT,
    chapter_order INT NOT NULL,
    word_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_order_per_story (story_id, chapter_order),
    INDEX idx_story_id (story_id)
);

-- Tabela de campos personalizados para artigos
CREATE TABLE article_custom_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_type ENUM('text', 'textarea', 'number', 'date', 'select', 'checkbox') NOT NULL,
    field_options TEXT, -- Para campos do tipo select (JSON)
    is_required BOOLEAN DEFAULT FALSE,
    field_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES article_categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_field_per_category (category_id, field_name),
    INDEX idx_category_id (category_id)
);

-- Tabela de valores dos campos personalizados
CREATE TABLE article_field_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    field_id INT NOT NULL,
    field_value TEXT,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (field_id) REFERENCES article_custom_fields(id) ON DELETE CASCADE,
    UNIQUE KEY unique_value_per_article_field (article_id, field_id),
    INDEX idx_article_id (article_id)
);

-- Tabela de mapas interativos
CREATE TABLE interactive_maps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    world_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    map_image_path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (world_id) REFERENCES worlds(id) ON DELETE CASCADE,
    INDEX idx_world_id (world_id)
);

-- Tabela de pinos dos mapas
CREATE TABLE map_pins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    map_id INT NOT NULL,
    article_id INT NOT NULL,
    x_coordinate DECIMAL(8,5) NOT NULL, -- Coordenada X em porcentagem (0-100)
    y_coordinate DECIMAL(8,5) NOT NULL, -- Coordenada Y em porcentagem (0-100)
    pin_color VARCHAR(7) DEFAULT '#FF0000',
    pin_icon VARCHAR(50) DEFAULT 'location',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (map_id) REFERENCES interactive_maps(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    INDEX idx_map_id (map_id)
);

-- Tabela de eventos da linha do tempo
CREATE TABLE timeline_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    world_id INT NOT NULL,
    article_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date VARCHAR(100), -- Formato flexível para datas ficcionais
    event_year INT, -- Ano para ordenação
    event_type ENUM('era', 'age', 'year', 'event') DEFAULT 'event',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (world_id) REFERENCES worlds(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE SET NULL,
    INDEX idx_world_id (world_id),
    INDEX idx_event_year (event_year)
);

-- Tabela de seções de sistemas de RPG
CREATE TABLE rpg_system_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rpg_system_id INT NOT NULL,
    section_name VARCHAR(100) NOT NULL,
    section_type ENUM('mechanics', 'attributes', 'skills', 'classes', 'spells', 'equipment', 'custom') NOT NULL,
    content LONGTEXT,
    section_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rpg_system_id) REFERENCES rpg_systems(id) ON DELETE CASCADE,
    INDEX idx_rpg_system_id (rpg_system_id)
);

-- Tabela de tokens de recuperação de senha
CREATE TABLE password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id)
);

-- Tabela de sessões de usuário (para "Lembrar-me")
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_user_id (user_id)
);

-- Inserção de categorias padrão (será executada após a criação de um mundo)
-- Esta inserção será feita via PHP quando um mundo for criado

-- Inserção de dados de exemplo para desenvolvimento (opcional)
-- INSERT INTO users (username, email, password_hash) VALUES 
-- ('admin', 'admin@codice.com', '$2y$10$example_hash_here');

-- Comentários sobre índices e otimizações:
-- 1. Índices foram criados nas colunas mais consultadas (user_id, world_id, etc.)
-- 2. Índices únicos garantem integridade dos dados
-- 3. Índice FULLTEXT na tabela articles permite busca textual eficiente
-- 4. Chaves estrangeiras com CASCADE garantem integridade referencial
-- 5. Campos de timestamp para auditoria e controle de versão

