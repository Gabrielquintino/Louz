<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use stdClass;

class FuncionarioModel extends DatabaseConfig
{
    public function listagem($booOnlyActive = true) : array {

        $strSqlFilter = $booOnlyActive ? "and f.status = 'ativo'" : " ";
        $sql = "SELECT f.id, c.nome cargo, f.nome, f.email, c.comissao, f.status
        FROM ". DB_USUARIO .".funcionarios f 
        INNER JOIN ". DB_USUARIO .".cargos c on 
        c.id = f.cargos_id and c.status = 'ativo' 
        WHERE 1 = 1 " . $strSqlFilter .
        "ORDER BY c.nome;";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }

    public function get($pIntId) {
        $sql = "SELECT f.id, c.nome cargo, f.nome, f.email, c.comissao, f.status
        FROM ". DB_USUARIO .".funcionarios f 
        INNER JOIN ". DB_USUARIO .".cargos c on 
        c.id = f.cargos_id and c.status = 'ativo' 
        WHERE f.id = " . $pIntId;
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }

    public function save($arrData) : int {

        if (isset($arrData['id']) && !empty($arrData['id'])) {

            $intId = $arrData['id'];
            unset($arrData['id']);
            
            // Construindo a parte SET dinamicamente
            $setParts = [];
            foreach ($arrData as $key => $value) {
                $setParts[] = "`$key` = ?";
            }
            $setClause = implode(", ", $setParts);

            // Construindo a query de UPDATE
            $sql = "UPDATE " . DB_USUARIO . ".eventos SET $setClause WHERE id = ?";
            $pdo = $this->getConnection()->prepare($sql);

            // Adicionando o id ao final do array de dados
            $arrData[] = $intId;

            try {
                $pdo->execute(array_values($arrData)); // Certificando-se de usar os valores do array
                return $intId;
            } catch (Exception $err) {
                throw new Exception($err);
            }  
        } else {

            $strNome = isset($arrData['nome']) ? mb_substr($arrData['nome'],0, 150) : null;
            $strEmail = isset($arrData['email']) ? mb_substr($arrData['email'],0, 150) : null;
            $strCargoId = $arrData['cargos_id'];
            $strStatus = isset($arrData['status']) ? $arrData['status'] : 'ativo';

            $sql = "INSERT INTO " . DB_USUARIO . ".funcionarios (`nome`, `email`, `cargos_id`, `status` ) VALUES (?, ?, ?, ?)";
            $pdo = $this->getConnection()->prepare($sql);

            try {
                $pdo->execute([$strNome, $strEmail, $strCargoId, $strStatus]);
                return $this->getConnection()->lastInsertId();
            } catch (Exception $err) {
                throw new Exception($err);
            }

        }


        return [];
    }

    public function delete($pIntId) : bool {
        $sql = "DELETE FROM ".DB_USUARIO.".agendamentos WHERE eventos_id = ?";
        $pdo = $this->getConnection()->prepare($sql);
        try {
            $pdo->execute([$pIntId]);
        } catch (Exception $err) {
            throw new Exception($err);
            return false;
        }

        $sql = "DELETE FROM " . DB_USUARIO . ".funcionarios WHERE id = ?";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$pIntId]); // Substitua $id pelo valor do ID que você deseja excluir
        } catch (Exception $err) {
            throw new Exception($err);
            return false;
        }
        
        return true;
    }    
}