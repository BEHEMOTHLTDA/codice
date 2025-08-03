# Códice do Criador

Uma plataforma web completa e robusta para criação de mundos, escrita colaborativa e desenvolvimento de sistemas de RPG. Desenvolvida com foco na excelência visual e experiência do usuário.

## 🌟 Características Principais

### 🎨 Design e UX
- **Interface Moderna**: Design elegante com modo claro/escuro
- **Responsivo**: Funciona perfeitamente em desktop, tablet e mobile
- **Micro-interações**: Animações sutis e transições suaves
- **Tipografia Premium**: Fonte Inter para máxima legibilidade

### 🌍 Construtor de Mundos
- **Sistema Wiki-Style**: Interconexão de artigos com links `@[Nome do Artigo]`
- **Categorias Organizadas**: Personagens, Locais, Itens, Criaturas, História, etc.
- **Editor Avançado**: Toolbar personalizada, formatação markdown, auto-save
- **Colaboração**: Sistema de permissões para trabalho em equipe

### 🤖 Assistente de IA
- **Sugestões Criativas**: Geração de ideias para artigos e conteúdo
- **Análise Inteligente**: Melhoria automática de textos
- **Gerador de Nomes**: Criação de nomes únicos para elementos do mundo
- **Integração OpenAI**: Powered by GPT para máxima qualidade

### ✍️ Estúdio de Escrita
- **Editor Profissional**: Ambiente dedicado para escrita de histórias
- **Gestão de Capítulos**: Organização estruturada de narrativas
- **Contador de Palavras**: Acompanhamento de progresso
- **Backup Automático**: Nunca perca seu trabalho

### 🎲 Sistemas de RPG
- **Criação de Sistemas**: Desenvolva regras personalizadas
- **Fichas de Personagem**: Templates flexíveis
- **Dados e Mecânicas**: Sistema completo de regras
- **Integração com Mundos**: Conecte sistemas aos seus universos

## 🚀 Tecnologias Utilizadas

### Backend
- **PHP 8+**: Linguagem principal do servidor
- **MySQL/MariaDB**: Banco de dados relacional
- **Arquitetura MVC**: Organização limpa do código
- **API REST**: Endpoints para integração

### Frontend
- **HTML5 Semântico**: Estrutura acessível
- **CSS3 Moderno**: Variáveis CSS, Grid, Flexbox
- **JavaScript ES6+**: Funcionalidades interativas
- **Design System**: Componentes reutilizáveis

### Integrações
- **OpenAI API**: Assistente de IA
- **Sistema de Upload**: Gestão de mídia
- **Autenticação Segura**: Sessões e permissões
- **Responsive Design**: Mobile-first approach

## 📁 Estrutura do Projeto

```
codice_do_criador/
├── index.php                 # Página inicial
├── login.php                 # Autenticação
├── register.php              # Cadastro
├── dashboard.php             # Painel principal
├── logout.php                # Logout
│
├── world-create.php          # Criar mundo
├── world-view.php            # Visualizar mundo
├── world-edit.php            # Editar mundo
│
├── article-create.php        # Criar artigo
├── article-view.php          # Visualizar artigo
├── article-edit.php          # Editar artigo
│
├── css/
│   └── style.css            # Estilos principais
│
├── js/
│   ├── main.js              # JavaScript principal
│   ├── editor.js            # Editor de artigos
│   └── ai-assistant.js      # Assistente de IA
│
├── php_includes/
│   ├── config.php           # Configurações
│   ├── Database.php         # Classe de banco
│   ├── User.php             # Gestão de usuários
│   ├── World.php            # Gestão de mundos
│   ├── Article.php          # Gestão de artigos
│   └── AIAssistant.php      # Assistente de IA
│
├── api/
│   └── ai-assistant.php     # API do assistente
│
├── assets/
│   └── images/              # Imagens do sistema
│
├── uploads/                 # Arquivos enviados
├── setup.sql               # Script de instalação
└── README.md               # Esta documentação
```

## 🛠️ Instalação

### Pré-requisitos
- PHP 8.0 ou superior
- MySQL 5.7+ ou MariaDB 10.3+
- Servidor web (Apache/Nginx)
- Extensões PHP: mysqli, curl, json, mbstring

### Passo a Passo

1. **Clone ou baixe o projeto**
   ```bash
   git clone [url-do-repositorio]
   cd codice_do_criador
   ```

2. **Configure o banco de dados**
   ```bash
   mysql -u root -p < setup.sql
   ```

3. **Configure as credenciais**
   Edite `php_includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'codice_criador');
   define('DB_USER', 'seu_usuario');
   define('DB_PASS', 'sua_senha');
   ```

4. **Configure a API de IA (opcional)**
   Adicione sua chave OpenAI:
   ```php
   $_ENV['OPENAI_API_KEY'] = 'sua_chave_aqui';
   ```

5. **Configure permissões**
   ```bash
   chmod 755 uploads/
   chmod 644 *.php
   ```

6. **Acesse a aplicação**
   Abra `http://localhost/codice_do_criador` no navegador

## 🎯 Funcionalidades Implementadas

### ✅ Módulo 1: Gestão de Usuários
- [x] Sistema de registro e login
- [x] Autenticação segura com sessões
- [x] Recuperação de senha
- [x] Perfis de usuário
- [x] Controle de permissões

### ✅ Módulo 2: Painel Principal
- [x] Dashboard com estatísticas
- [x] Visão geral dos projetos
- [x] Atividade recente
- [x] Navegação intuitiva
- [x] Cards informativos

### ✅ Módulo 3: Construtor de Mundos
- [x] Criação e edição de mundos
- [x] Sistema de categorias
- [x] Editor de artigos avançado
- [x] Links wiki-style (@[Nome])
- [x] Sistema de interconexão
- [x] Backlinks automáticos

### ✅ Módulo 4: Gestão de Mídia
- [x] Upload de imagens
- [x] Galeria de mídia
- [x] Integração com editor
- [x] Otimização automática
- [x] Controle de tamanho

### ✅ Módulo 5: Colaboração
- [x] Sistema de colaboradores
- [x] Permissões (leitor/editor)
- [x] Convites por email
- [x] Gestão de equipe
- [x] Controle de acesso

### ✅ Módulo 6: Assistente de IA
- [x] Sugestões de conteúdo
- [x] Geração de ideias
- [x] Criação de nomes
- [x] Análise de texto
- [x] Interface integrada

### ✅ Módulo 7: Interface e UX
- [x] Design responsivo
- [x] Modo claro/escuro
- [x] Animações suaves
- [x] Navegação intuitiva
- [x] Componentes reutilizáveis

## 🎨 Sistema de Design

### Paleta de Cores
```css
/* Cores Primárias */
--color-primary: #3b82f6;
--color-primary-hover: #2563eb;
--color-primary-light: #dbeafe;

/* Cores de Superfície */
--color-background: #ffffff;
--color-surface: #f8fafc;
--color-surface-elevated: #ffffff;

/* Cores de Texto */
--color-text: #1e293b;
--color-text-secondary: #64748b;
--color-text-muted: #94a3b8;

/* Cores de Estado */
--color-success: #10b981;
--color-warning: #f59e0b;
--color-error: #ef4444;
```

### Tipografia
- **Fonte Principal**: Inter (Google Fonts)
- **Tamanhos**: 12px, 14px, 16px, 18px, 20px, 24px, 32px
- **Pesos**: 300, 400, 500, 600, 700

### Componentes
- **Botões**: 4 variações (primary, secondary, ghost, danger)
- **Cards**: Elevação sutil com bordas arredondadas
- **Formulários**: Campos consistentes com validação
- **Modais**: Overlay com animação suave

## 🔧 Configurações Avançadas

### Limites do Sistema
```php
// Limites por usuário
define('MAX_WORLDS_PER_USER', 5);
define('MAX_STORIES_PER_USER', 10);
define('MAX_ARTICLES_PER_WORLD', 500);
define('MAX_COLLABORATORS_PER_WORLD', 10);

// Limites de conteúdo
define('MAX_ARTICLE_TITLE_LENGTH', 200);
define('MAX_ARTICLE_CONTENT_LENGTH', 50000);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
```

### Configurações de IA
```php
// OpenAI Configuration
$_ENV['OPENAI_API_KEY'] = 'sua_chave';
$_ENV['OPENAI_API_BASE'] = 'https://api.openai.com/v1';

// Limites de uso
define('AI_MAX_REQUESTS_PER_HOUR', 50);
define('AI_MAX_TOKENS_PER_REQUEST', 1000);
```

## 🚀 Deploy e Produção

### Servidor Web
```apache
# .htaccess
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Segurança
<Files "*.php">
    Order allow,deny
    Allow from all
</Files>

<Files "config.php">
    Order deny,allow
    Deny from all
</Files>
```

### Otimizações
- **Compressão Gzip**: Reduz tamanho dos arquivos
- **Cache de Navegador**: Headers de cache apropriados
- **Minificação**: CSS e JS otimizados
- **CDN**: Para assets estáticos

### Backup
```bash
# Backup do banco de dados
mysqldump -u usuario -p codice_criador > backup_$(date +%Y%m%d).sql

# Backup dos arquivos
tar -czf backup_files_$(date +%Y%m%d).tar.gz uploads/ assets/
```

## 🔒 Segurança

### Medidas Implementadas
- **Sanitização de Entrada**: Todos os inputs são filtrados
- **Prepared Statements**: Proteção contra SQL Injection
- **CSRF Protection**: Tokens de segurança em formulários
- **Validação de Sessão**: Verificação de autenticidade
- **Upload Seguro**: Validação de tipos de arquivo

### Boas Práticas
- Senhas hasheadas com password_hash()
- Sessões seguras com configurações apropriadas
- Headers de segurança (X-Frame-Options, etc.)
- Validação tanto no frontend quanto backend

## 📱 Responsividade

### Breakpoints
```css
/* Mobile First */
@media (min-width: 640px) { /* sm */ }
@media (min-width: 768px) { /* md */ }
@media (min-width: 1024px) { /* lg */ }
@media (min-width: 1280px) { /* xl */ }
```

### Componentes Adaptativos
- **Sidebar**: Collapsa em mobile
- **Cards**: Grid responsivo
- **Formulários**: Campos adaptáveis
- **Tabelas**: Scroll horizontal quando necessário

## 🎯 Roadmap Futuro

### Funcionalidades Planejadas
- [ ] **Mapas Interativos**: Visualização geográfica dos mundos
- [ ] **Linhas do Tempo**: Cronologia visual de eventos
- [ ] **Sistema de Versioning**: Histórico de mudanças
- [ ] **Exportação**: PDF, EPUB, Word
- [ ] **API Pública**: Integração com outras ferramentas
- [ ] **Plugins**: Sistema de extensões
- [ ] **Temas Personalizados**: Customização visual
- [ ] **Modo Offline**: PWA com cache

### Melhorias Técnicas
- [ ] **Performance**: Otimização de queries
- [ ] **Cache**: Sistema de cache inteligente
- [ ] **CDN**: Distribuição de conteúdo
- [ ] **Monitoramento**: Logs e métricas
- [ ] **Testes**: Cobertura automatizada

## 🤝 Contribuição

### Como Contribuir
1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

### Padrões de Código
- **PHP**: PSR-12 coding standard
- **JavaScript**: ES6+ com comentários JSDoc
- **CSS**: BEM methodology
- **Commits**: Conventional Commits

## 📄 Licença

Este projeto está licenciado sob a MIT License - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🙏 Agradecimentos

- **OpenAI**: Pela API de IA que potencializa o assistente criativo
- **Google Fonts**: Pela tipografia Inter
- **Comunidade Open Source**: Pelas inspirações e ferramentas

## 📞 Suporte

Para suporte, dúvidas ou sugestões:
- **Email**: suporte@codicedocriador.com
- **Discord**: [Servidor da Comunidade]
- **GitHub Issues**: Para bugs e feature requests

---

**Códice do Criador** - Transformando ideias em mundos extraordinários. ✨

