<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use stdClass;

class EventoModel extends DatabaseConfig
{
    public function listagem() : array {
        $sql = "SELECT * FROM ". $_SESSION['db_usuario'] .".eventos WHERE status = 'ativo'";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }
}