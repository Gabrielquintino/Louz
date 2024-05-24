<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use stdClass;

class FuncionarioModel extends DatabaseConfig
{
    public function listagem() : array {
        $sql = "SELECT f.id, c.nome cargo, f.nome, f.email 
        FROM ". $_SESSION['db_usuario'] .".funcionarios f 
        INNER JOIN ". $_SESSION['db_usuario'] .".cargos c on 
        c.id = f.cargos_id and c.status = 'ativo' 
        WHERE f.status and f.status = 'ativo'
        ORDER BY c.nome;";
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