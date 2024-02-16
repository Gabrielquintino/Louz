<?php

namespace App\Config;

use PDO;
use PDOException;

class DatabaseConfig
{
    
    private $dsn = "mysql:host=". DB_HOST .";dbname=" . DB_BASE;
    private $usuario = DB_USER;
    private $senha = DB_PASSWORD;

    public function getConnection()
    {
        try {
            return new PDO($this->dsn, $this->usuario, $this->senha);
        } catch (PDOException $e) {
            die('Erro na conexão com o banco de dados: ' . $e->getMessage());
        }
    }
}
