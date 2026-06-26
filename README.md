# Anotações


## Requirements


### Composer

Gerenciador de pacotes https://getcomposer.org/


### httpie

Docs: https://httpie.io/docs/cli/request-url

```bash
# Install httpie
curl -SsL https://packages.httpie.io/deb/KEY.gpg | sudo gpg --dearmor -o /usr/share/keyrings/httpie.gpg
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/httpie.gpg] https://packages.httpie.io/deb ./" | sudo tee /etc/apt/sources.list.d/httpie.list > /dev/null
sudo apt update
sudo apt install httpie

# Upgrade httpie
sudo apt update && sudo apt upgrade httpie

```


## Run Containers


docker compose up -d


## DB Connection

http://localhost:8083/?server=mariadb&username=appuser&db=appdb

Servidor: mariadb
Usuário: appuser
Pass: <.env>
Banco de Dados: appdb


## Pacotes


### vlucas/phpdotenv
https://packagist.org/packages/vlucas/phpdotenv
https://github.com/vlucas/phpdotenv
pkg:composer/vlucas/phpdotenv

```php
composer require vlucas/phpdotenv
```

### guzzlehttp/guzzle
https://packagist.org/packages/guzzlehttp/guzzle
https://github.com/guzzle/guzzle
pkg:composer/guzzlehttp/guzzle

```php
composer require guzzlehttp/guzzle
```

### stripe/stripe-php 
https://packagist.org/packages/stripe/stripe-php
https://github.com/stripe/stripe-php
https://stripe.com/

```php 
Composer require stripe/stripe-php 
```


## Referências

https://www.php.net/manual/en/function.json-decode.php
https://docs.guzzlephp.org/en/stable/overview.html
https://docs.guzzlephp.org/en/stable/
https://www.php.net/manual/en/book.curl.php
https://www.php.net/manual/en/function.var-dump.php
https://picocss.com/docs
https://docs.github.com/en/rest/using-the-rest-api/getting-started-with-the-rest-api?apiVersion=2026-03-10
https://randomuser.me/
https://www.php.net/manual/en/function.password-hash.php
https://www.iana.org/assignments/jwt/jwt.xhtml

