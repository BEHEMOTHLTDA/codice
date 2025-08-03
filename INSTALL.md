# Guia de Instalação - Códice do Criador

Este guia fornece instruções detalhadas para instalar e configurar o Códice do Criador em diferentes ambientes.

## 📋 Pré-requisitos

### Requisitos do Sistema
- **PHP**: 8.0 ou superior
- **Banco de Dados**: MySQL 5.7+ ou MariaDB 10.3+
- **Servidor Web**: Apache 2.4+ ou Nginx 1.18+
- **Memória**: Mínimo 512MB RAM
- **Espaço em Disco**: Mínimo 1GB livre

### Extensões PHP Necessárias
```bash
# Verificar extensões instaladas
php -m | grep -E "(mysqli|curl|json|mbstring|gd|zip)"
```

Extensões obrigatórias:
- `mysqli` - Conexão com MySQL
- `curl` - Requisições HTTP (API de IA)
- `json` - Manipulação de JSON
- `mbstring` - Strings multibyte
- `gd` - Manipulação de imagens
- `zip` - Compressão de arquivos

## 🚀 Instalação Rápida

### 1. Download do Projeto
```bash
# Via Git (recomendado)
git clone https://github.com/seu-usuario/codice-do-criador.git
cd codice-do-criador

# Ou baixe o ZIP e extraia
wget https://github.com/seu-usuario/codice-do-criador/archive/main.zip
unzip main.zip
cd codice-do-criador-main
```

### 2. Configuração do Banco de Dados
```bash
# Conectar ao MySQL
mysql -u root -p

# Criar banco de dados
CREATE DATABASE codice_criador CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Criar usuário (opcional, mas recomendado)
CREATE USER 'codice_user'@'localhost' IDENTIFIED BY 'senha_segura';
GRANT ALL PRIVILEGES ON codice_criador.* TO 'codice_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importar estrutura
mysql -u codice_user -p codice_criador < setup.sql
```

### 3. Configuração da Aplicação
```bash
# Copiar arquivo de configuração
cp php_includes/config.example.php php_includes/config.php

# Editar configurações
nano php_includes/config.php
```

### 4. Configurar Permissões
```bash
# Permissões de diretórios
chmod 755 uploads/
chmod 755 assets/
chmod 644 *.php
chmod 644 css/*.css
chmod 644 js/*.js

# Propriedade do servidor web (Ubuntu/Debian)
sudo chown -R www-data:www-data .

# Ou para CentOS/RHEL
sudo chown -R apache:apache .
```

### 5. Configuração do Servidor Web

#### Apache
```apache
# /etc/apache2/sites-available/codice-criador.conf
<VirtualHost *:80>
    ServerName codice-criador.local
    DocumentRoot /var/www/html/codice-do-criador
    
    <Directory /var/www/html/codice-do-criador>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/codice-criador_error.log
    CustomLog ${APACHE_LOG_DIR}/codice-criador_access.log combined
</VirtualHost>
```

```bash
# Ativar site
sudo a2ensite codice-criador.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

#### Nginx
```nginx
# /etc/nginx/sites-available/codice-criador
server {
    listen 80;
    server_name codice-criador.local;
    root /var/www/html/codice-do-criador;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
}
```

```bash
# Ativar site
sudo ln -s /etc/nginx/sites-available/codice-criador /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## ⚙️ Configuração Detalhada

### Arquivo de Configuração Principal
```php
<?php
// php_includes/config.php

// === CONFIGURAÇÕES DO BANCO DE DADOS ===
define('DB_HOST', 'localhost');
define('DB_NAME', 'codice_criador');
define('DB_USER', 'codice_user');
define('DB_PASS', 'sua_senha_aqui');
define('DB_CHARSET', 'utf8mb4');

// === CONFIGURAÇÕES DO SITE ===
define('SITE_NAME', 'Códice do Criador');
define('SITE_URL', 'http://localhost/codice-do-criador');
define('SITE_EMAIL', 'admin@codicedocriador.com');

// === CONFIGURAÇÕES DE SEGURANÇA ===
define('SECRET_KEY', 'gere_uma_chave_aleatoria_aqui');
define('SESSION_LIFETIME', 3600 * 24 * 7); // 7 dias

// === CONFIGURAÇÕES DE UPLOAD ===
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// === CONFIGURAÇÕES DE IA ===
$_ENV['OPENAI_API_KEY'] = 'sua_chave_openai_aqui';
$_ENV['OPENAI_API_BASE'] = 'https://api.openai.com/v1';

// === LIMITES DO SISTEMA ===
define('MAX_WORLDS_PER_USER', 5);
define('MAX_STORIES_PER_USER', 10);
define('MAX_ARTICLES_PER_WORLD', 500);
define('MAX_COLLABORATORS_PER_WORLD', 10);

// === CONFIGURAÇÕES DE EMAIL ===
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'seu_email@gmail.com');
define('SMTP_PASS', 'sua_senha_app');
define('SMTP_ENCRYPTION', 'tls');
?>
```

### Configuração da API OpenAI
1. **Obter Chave da API**:
   - Acesse https://platform.openai.com/
   - Crie uma conta ou faça login
   - Vá para API Keys e gere uma nova chave
   - Copie a chave gerada

2. **Configurar no Sistema**:
   ```php
   $_ENV['OPENAI_API_KEY'] = 'sk-proj-...sua_chave_aqui';
   ```

3. **Testar Integração**:
   ```bash
   # Teste via linha de comando
   curl -H "Authorization: Bearer sua_chave" \
        -H "Content-Type: application/json" \
        -d '{"model":"gpt-3.5-turbo","messages":[{"role":"user","content":"Hello"}]}' \
        https://api.openai.com/v1/chat/completions
   ```

## 🐳 Instalação com Docker

### Dockerfile
```dockerfile
FROM php:8.1-apache

# Instalar extensões PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libzip-dev \
    libpng-dev \
    && docker-php-ext-install curl zip gd

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar aplicação
COPY . /var/www/html/

# Configurar permissões
RUN chown -R www-data:www-data /var/www/html/
RUN chmod 755 /var/www/html/uploads/

EXPOSE 80
```

### docker-compose.yml
```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./uploads:/var/www/html/uploads
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=codice_criador
      - DB_USER=root
      - DB_PASS=rootpassword

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: codice_criador
    volumes:
      - db_data:/var/lib/mysql
      - ./setup.sql:/docker-entrypoint-initdb.d/setup.sql
    ports:
      - "3306:3306"

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    ports:
      - "8081:80"
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: rootpassword

volumes:
  db_data:
```

### Comandos Docker
```bash
# Construir e iniciar
docker-compose up -d

# Verificar logs
docker-compose logs web

# Parar serviços
docker-compose down

# Backup do banco
docker exec -it codice_db mysqldump -u root -p codice_criador > backup.sql
```

## 🔧 Configurações Avançadas

### Otimização do PHP
```ini
; php.ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
max_input_vars = 3000

; OPcache (recomendado para produção)
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
```

### Configuração de SSL/HTTPS
```apache
# Apache SSL
<VirtualHost *:443>
    ServerName codice-criador.com
    DocumentRoot /var/www/html/codice-do-criador
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    # Redirecionar HTTP para HTTPS
    <Directory /var/www/html/codice-do-criador>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:80>
    ServerName codice-criador.com
    Redirect permanent / https://codice-criador.com/
</VirtualHost>
```

### Configuração de Cache
```apache
# .htaccess - Cache de arquivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
</IfModule>

# Compressão Gzip
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

## 🧪 Testes e Verificação

### Verificar Instalação
```bash
# Teste de conectividade do banco
php -r "
$pdo = new PDO('mysql:host=localhost;dbname=codice_criador', 'user', 'pass');
echo 'Conexão com banco: OK\n';
"

# Teste de extensões PHP
php -r "
echo 'mysqli: ' . (extension_loaded('mysqli') ? 'OK' : 'ERRO') . '\n';
echo 'curl: ' . (extension_loaded('curl') ? 'OK' : 'ERRO') . '\n';
echo 'gd: ' . (extension_loaded('gd') ? 'OK' : 'ERRO') . '\n';
"

# Teste de permissões
ls -la uploads/
```

### Checklist de Instalação
- [ ] PHP 8.0+ instalado e configurado
- [ ] Extensões PHP necessárias ativas
- [ ] Banco de dados criado e importado
- [ ] Arquivo de configuração editado
- [ ] Permissões de arquivo configuradas
- [ ] Servidor web configurado
- [ ] Chave OpenAI configurada (opcional)
- [ ] SSL configurado (produção)
- [ ] Backup configurado

### Teste de Funcionalidades
1. **Acesso à aplicação**: http://localhost/codice-do-criador
2. **Registro de usuário**: Criar conta de teste
3. **Login**: Autenticar com credenciais
4. **Criar mundo**: Testar funcionalidade básica
5. **Criar artigo**: Testar editor
6. **Assistente de IA**: Testar se configurado

## 🚨 Solução de Problemas

### Problemas Comuns

#### Erro de Conexão com Banco
```
SQLSTATE[HY000] [2002] Connection refused
```
**Solução**:
- Verificar se MySQL está rodando: `sudo systemctl status mysql`
- Verificar credenciais em config.php
- Testar conexão manual: `mysql -u usuario -p`

#### Erro de Permissões
```
Warning: file_put_contents(): Permission denied
```
**Solução**:
```bash
sudo chown -R www-data:www-data uploads/
chmod 755 uploads/
```

#### Erro 500 - Internal Server Error
**Solução**:
- Verificar logs do Apache: `tail -f /var/log/apache2/error.log`
- Verificar sintaxe PHP: `php -l index.php`
- Verificar .htaccess

#### Assistente de IA não funciona
**Solução**:
- Verificar chave OpenAI em config.php
- Testar conectividade: `curl https://api.openai.com/v1/models`
- Verificar logs de erro

### Logs e Debugging
```bash
# Logs do Apache
tail -f /var/log/apache2/error.log

# Logs do PHP
tail -f /var/log/php/error.log

# Logs personalizados da aplicação
tail -f logs/application.log
```

### Modo Debug
```php
// Adicionar ao início de config.php para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

## 📞 Suporte

Se você encontrar problemas durante a instalação:

1. **Verifique a documentação**: README.md
2. **Consulte os logs**: Sempre verifique os logs de erro
3. **Teste isoladamente**: Teste cada componente separadamente
4. **Comunidade**: Abra uma issue no GitHub
5. **Suporte direto**: suporte@codicedocriador.com

## 🔄 Atualizações

### Atualizar o Sistema
```bash
# Backup antes de atualizar
cp -r . ../backup-$(date +%Y%m%d)
mysqldump -u user -p codice_criador > backup-$(date +%Y%m%d).sql

# Atualizar código
git pull origin main

# Executar migrações se houver
php migrate.php

# Limpar cache se necessário
rm -rf cache/*
```

---

**Instalação concluída!** 🎉 Agora você pode começar a criar mundos extraordinários com o Códice do Criador.

