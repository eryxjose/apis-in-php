<?php

//  https://www.php.net/manual/en/language.oop5.decon.php

class Database 
{
    public function __construct (
        private string $host, 
        private string $name, 
        private string $user, 
        private string $password
    ) {
    }

    public function getConnection()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";

        return new PDO($dsn, $this->user, $this->$password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
}