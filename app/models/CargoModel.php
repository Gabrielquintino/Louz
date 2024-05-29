<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;

class CargoModel extends DatabaseConfig{

    public function listagem($pBoolOnlyActive = false) : array {
        $strSqlFilter = $pBoolOnlyActive ? "and c.status = 'ativo' " : "";

        $sql = "SELECT *
        FROM " . DB_USUARIO . ".cargos c
        WHERE 1=1 " . $strSqlFilter;
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }

    public function delete($pId, $pNewId) {
        $sql = "UPDATE ".DB_USUARIO.".`funcionarios` SET `cargos_id` = ? WHERE `cargos_id` = ?";

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$pNewId, $pId]);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        $sql = "DELETE FROM ".DB_USUARIO.".cargos WHERE id = ?;";

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$pId]);
            return ['success' => true, 'id' => $pId];
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

}