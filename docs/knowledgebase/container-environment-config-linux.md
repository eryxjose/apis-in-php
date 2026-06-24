# Container environment config for php/mariadb

Abordagem recomendada:

```text
Não instalar MariaDB no Ubuntu via apt.
Usar MariaDB em Docker Compose por projeto.
Publicar a porta somente em 127.0.0.1.
Manter o código PHP dentro da sua pasta ~/dev ou ~/repos.
Evitar XAMPP.
```

Isso combina melhor com sua máquina, porque você já trabalha com **Docker, .NET microservices, Next.js, Python e bancos diferentes**. Assim cada projeto PHP pode ter seu próprio banco, versão e volume sem misturar tudo no sistema.

A imagem oficial do MariaDB exige uma senha de root ou outra opção equivalente na primeira inicialização, como `MARIADB_ROOT_PASSWORD`. Ela também permite criar banco e usuário inicial via variáveis de ambiente. ([Docker Hub][1])

---

# 1. Estrutura recomendada para seus projetos

Como você tem várias tecnologias, eu sugiro separar por ecossistema, mas mantendo um padrão parecido:

```text
~/dev/
├── php/
│   ├── estudos/
│   │   └── php-mariadb-demo/
│   ├── wordpress/
│   │   └── site-cliente-x/
│   └── laravel/
│       └── app-exemplo/
│
├── dotnet/
│   ├── carsties-solution/
│   └── expenses-solution/
│
├── node/
│   ├── nextjs/
│   └── angular/
│
├── python/
│   ├── scripts/
│   └── estudos/
│
└── infra/
    ├── scripts/
    └── shared/
```

Ou, se você prefere separar por origem/curso/cliente:

```text
~/dev/
├── linkedin-learning/
├── udemy-courses/
├── clientes/
├── estudos/
└── infra/
```

Para o seu caso, eu gosto mais desta abordagem híbrida:

```text
~/dev/
├── estudos/
│   ├── php/
│   ├── dotnet/
│   ├── python/
│   └── frontend/
│
├── clientes/
│   ├── cliente-a/
│   └── cliente-b/
│
├── cursos/
│   ├── linkedin-learning/
│   └── udemy/
│
└── infra/
    ├── scripts/
    └── templates/
```

Exemplo específico para PHP:

```text
~/dev/estudos/php/php-mariadb-demo/
├── app/
│   └── public/
│       └── index.php
├── infra/
│   └── mariadb/
│       └── init/
│           └── 001-create-tables.sql
├── docker-compose.yml
├── .env
├── .gitignore
└── README.md
```

Essa estrutura evita misturar PHP com Apache do sistema, bancos locais e configurações globais.

---

# 2. Criar projeto PHP de exemplo

Crie a pasta:

```bash
mkdir -p ~/dev/estudos/php/php-mariadb-demo
cd ~/dev/estudos/php/php-mariadb-demo
```

Crie as subpastas:

```bash
mkdir -p app/public
mkdir -p infra/mariadb/init
```

---

# 3. Criar arquivo `.env`

Crie:

```bash
nano .env
```

Conteúdo:

```env
COMPOSE_PROJECT_NAME=php_mariadb_demo

MARIADB_ROOT_PASSWORD=root_dev_password
MARIADB_DATABASE=appdb
MARIADB_USER=appuser
MARIADB_PASSWORD=app_dev_password

APP_PORT=8082
DB_PORT=3307
```

Para desenvolvimento local está bom. Em projeto real, não envie esse `.env` para repositório público.

---

# 4. Criar `docker-compose.yml`

Crie:

```bash
nano docker-compose.yml
```

Conteúdo:

```yaml
services:
  php:
    image: php:8.3-apache
    container_name: php_mariadb_demo_app
    restart: unless-stopped
    ports:
      - "127.0.0.1:${APP_PORT}:80"
    volumes:
      - ./app/public:/var/www/html
    depends_on:
      - mariadb
    networks:
      - php_mariadb_demo_net

  mariadb:
    image: mariadb:11.4
    container_name: php_mariadb_demo_db
    restart: unless-stopped
    ports:
      - "127.0.0.1:${DB_PORT}:3306"
    environment:
      MARIADB_ROOT_PASSWORD: ${MARIADB_ROOT_PASSWORD}
      MARIADB_DATABASE: ${MARIADB_DATABASE}
      MARIADB_USER: ${MARIADB_USER}
      MARIADB_PASSWORD: ${MARIADB_PASSWORD}
    volumes:
      - mariadb_data:/var/lib/mysql
      - ./infra/mariadb/init:/docker-entrypoint-initdb.d:ro
    networks:
      - php_mariadb_demo_net

  adminer:
    image: adminer:latest
    container_name: php_mariadb_demo_adminer
    restart: unless-stopped
    ports:
      - "127.0.0.1:8083:8080"
    depends_on:
      - mariadb
    networks:
      - php_mariadb_demo_net

volumes:
  mariadb_data:

networks:
  php_mariadb_demo_net:
    driver: bridge
```

O detalhe mais importante para sua segurança é este:

```yaml
ports:
  - "127.0.0.1:${DB_PORT}:3306"
```

Isso publica a porta apenas no loopback da sua máquina. A documentação do Docker explica que portas publicadas podem ficar acessíveis pelo host/rede dependendo do binding usado; por isso, usar `127.0.0.1` é a forma correta para ambiente local restrito. ([Docker Documentation][2])

---

# 5. Criar script SQL inicial

Crie:

```bash
nano infra/mariadb/init/001-create-tables.sql
```

Conteúdo:

```sql
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (name, price)
VALUES 
    ('Produto de exemplo 1', 49.90),
    ('Produto de exemplo 2', 89.90);
```

A pasta `docker-entrypoint-initdb.d` é executada somente na primeira criação do volume do banco. Se você mudar esse SQL depois que o volume já existe, ele não será executado novamente. Nesse caso, precisa remover o volume ou rodar o SQL manualmente.

---

# 6. Criar arquivo PHP de teste

Crie:

```bash
nano app/public/index.php
```

Conteúdo:

```php
<?php

$host = 'mariadb';
$dbname = 'appdb';
$user = 'appuser';
$password = 'app_dev_password';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $stmt = $pdo->query("SELECT id, name, price, created_at FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erro ao conectar ao banco: " . htmlspecialchars($e->getMessage());
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>PHP + MariaDB Demo</title>
</head>
<body>
    <h1>Produtos</h1>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Criado em</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['id']) ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td>R$ <?= htmlspecialchars($product['price']) ?></td>
                    <td><?= htmlspecialchars($product['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
```

Mas ainda falta um detalhe: a imagem `php:8.3-apache` não vem necessariamente com a extensão PDO MySQL ativa. Então a melhor abordagem é criar seu próprio `Dockerfile`.

---

# 7. Melhorar com `Dockerfile` para PHP

Crie:

```bash
nano Dockerfile
```

Conteúdo:

```dockerfile
FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite
```

Agora ajuste o `docker-compose.yml`.

Troque isto:

```yaml
php:
  image: php:8.3-apache
```

por:

```yaml
php:
  build:
    context: .
    dockerfile: Dockerfile
```

Fica assim:

```yaml
services:
  php:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: php_mariadb_demo_app
    restart: unless-stopped
    ports:
      - "127.0.0.1:${APP_PORT}:80"
    volumes:
      - ./app/public:/var/www/html
    depends_on:
      - mariadb
    networks:
      - php_mariadb_demo_net

  mariadb:
    image: mariadb:11.4
    container_name: php_mariadb_demo_db
    restart: unless-stopped
    ports:
      - "127.0.0.1:${DB_PORT}:3306"
    environment:
      MARIADB_ROOT_PASSWORD: ${MARIADB_ROOT_PASSWORD}
      MARIADB_DATABASE: ${MARIADB_DATABASE}
      MARIADB_USER: ${MARIADB_USER}
      MARIADB_PASSWORD: ${MARIADB_PASSWORD}
    volumes:
      - mariadb_data:/var/lib/mysql
      - ./infra/mariadb/init:/docker-entrypoint-initdb.d:ro
    networks:
      - php_mariadb_demo_net

  adminer:
    image: adminer:latest
    container_name: php_mariadb_demo_adminer
    restart: unless-stopped
    ports:
      - "127.0.0.1:8083:8080"
    depends_on:
      - mariadb
    networks:
      - php_mariadb_demo_net

volumes:
  mariadb_data:

networks:
  php_mariadb_demo_net:
    driver: bridge
```

---

# 8. Subir os containers

Execute:

```bash
docker compose up -d --build
```

Verifique:

```bash
docker compose ps
```

Veja logs:

```bash
docker compose logs -f
```

Acesse a aplicação:

```text
http://localhost:8082
```

Acesse o Adminer:

```text
http://localhost:8083
```

Dados para login no Adminer:

```text
Sistema: MySQL
Servidor: mariadb
Usuário: appuser
Senha: app_dev_password
Base de dados: appdb
```

Dentro de um container Docker Compose, você usa o nome do serviço como host. Por isso o PHP conecta em:

```php
$host = 'mariadb';
```

e não em `localhost`.

---

# 9. Testar conexão pelo terminal

Entrar no container do banco:

```bash
docker compose exec mariadb mariadb -u appuser -p appdb
```

Digite a senha:

```text
app_dev_password
```

Depois:

```sql
SHOW TABLES;
SELECT * FROM products;
```

Sair:

```sql
exit;
```

---

# 10. Parar e remover

Para parar sem apagar banco:

```bash
docker compose down
```

Para apagar banco e recriar do zero:

```bash
docker compose down -v
docker compose up -d --build
```

Atenção: `-v` remove o volume `mariadb_data`, então os dados serão apagados.

---

# 11. `.gitignore` recomendado

Crie:

```bash
nano .gitignore
```

Conteúdo:

```gitignore
.env
/vendor/
node_modules/
.DS_Store
.idea/
.vscode/
*.log
```

Se quiser versionar um exemplo do `.env`, crie:

```bash
nano .env.example
```

Conteúdo:

```env
COMPOSE_PROJECT_NAME=php_mariadb_demo

MARIADB_ROOT_PASSWORD=change_me
MARIADB_DATABASE=appdb
MARIADB_USER=appuser
MARIADB_PASSWORD=change_me

APP_PORT=8082
DB_PORT=3307
```

---

# 12. Como isso fica junto com Apache instalado via apt?

Como você já instalou `apache2` via apt, há duas possibilidades.

## Abordagem A — Apache do sistema para sites simples

Você pode deixar o Apache do Ubuntu para testes simples em PHP, escutando só em `127.0.0.1`, como conversamos antes.

Exemplo:

```text
Apache apt:
http://localhost:8080
```

E deixar os projetos Docker em outras portas:

```text
PHP Docker demo:
http://localhost:8082

Adminer:
http://localhost:8083
```

## Abordagem B — Tudo PHP em Docker

Essa é a abordagem que eu mais recomendo para você.

Nesse caso, o Apache instalado via `apt` pode ficar parado:

```bash
sudo systemctl stop apache2
sudo systemctl disable apache2
```

E cada projeto PHP sobe seu próprio ambiente:

```bash
docker compose up -d
```

Essa opção reduz conflito de porta e evita manter serviço web permanente rodando no Ubuntu.

---

# 13. Melhor abordagem para seu ambiente

Para sua máquina, eu usaria este padrão:

```text
Sistema Ubuntu:
├── Docker
├── Git
├── VSCode
├── NVM/Node
├── .NET SDK
├── Python/venv/pyenv se necessário
├── Composer opcional no host
└── Sem bancos instalados via apt
```

E por projeto:

```text
Projeto PHP:
├── PHP/Apache ou PHP-FPM em container
├── MariaDB em container
├── Adminer/phpMyAdmin em container opcional
├── Volume próprio para banco
└── Porta publicada só em 127.0.0.1
```

Projeto .NET:

```text
Projeto .NET:
├── APIs no host ou containers
├── Postgres/SQL Server/RabbitMQ/MongoDB em containers
├── Next.js no host ou container
└── docker-compose específico do projeto
```

Projeto Python:

```text
Projeto Python:
├── venv local
├── requirements.txt ou pyproject.toml
├── banco em Docker se precisar
└── sem instalar dependências globais desnecessárias
```

---

# 14. Regra que eu seguiria daqui pra frente

A regra mais saudável para seu ambiente seria:

```text
Linguagens e SDKs podem ficar no Ubuntu.
Bancos, filas, caches e serviços auxiliares ficam em Docker.
Cada projeto tem seu próprio docker-compose.yml.
Portas sempre publicadas em 127.0.0.1, não em 0.0.0.0.
```

Exemplo bom:

```yaml
ports:
  - "127.0.0.1:3307:3306"
```

Exemplo que eu evitaria no seu cenário:

```yaml
ports:
  - "3307:3306"
```

Porque esse segundo formato pode expor o serviço em interfaces além do loopback, dependendo da configuração do Docker/host. A documentação do Docker trata justamente do comportamento de publicação de portas e do binding de endereço. ([Docker Documentation][2])

---

# 15. Estrutura final recomendada para o exemplo

No final, seu projeto ficaria assim:

```text
php-mariadb-demo/
├── app/
│   └── public/
│       └── index.php
├── infra/
│   └── mariadb/
│       └── init/
│           └── 001-create-tables.sql
├── Dockerfile
├── docker-compose.yml
├── .env
├── .env.example
├── .gitignore
└── README.md
```

Essa é uma base simples, segura e compatível com o jeito que você já trabalha com Docker nos projetos .NET.

[1]: https://hub.docker.com/_/mariadb?utm_source=chatgpt.com "mariadb - Official Image"
[2]: https://docs.docker.com/engine/network/port-publishing/?utm_source=chatgpt.com "Port publishing and mapping"
