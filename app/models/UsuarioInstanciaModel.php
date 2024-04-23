<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use Exception;
use PDO;

class UsuarioInstanciaModel extends DatabaseConfig {

    public function getInstance(bool $pBooOnlyConected = false, string $pStrInstance = '') : array {


        $strSqlFilter = " WHERE instancia = '" . $pStrInstance . "'";

        
        $sql = "SELECT * FROM " . DB_BASE . ".usuarios_instancias " . $strSqlFilter;
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);

            if (!defined('USER_ID') && !empty($result)) {
                $sql = "SELECT * FROM " . DB_BASE . ".usuarios WHERE id = :id";
                $pdo = $this->getConnection()->prepare($sql);
                $pdo->bindParam(':id', $result[0]['usuario_id']);
                $pdo->execute();
                $resultado = $pdo->fetchAll(PDO::FETCH_ASSOC);

                if (empty($resultado)) {
                    throw new Exception("Usuário não encontrado");
                }

                define('USER_ID', $resultado[0]["id"]);
                define('DB_USUARIO', 'db_' . $resultado[0]["codigo"]);

                $strSqlFilter = " WHERE usuario_id = " . USER_ID;
                $strSqlFilter .= $pBooOnlyConected ? " and status = 'conectado'" : "";
            }

        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }

    public function updateInstance(string $pStrStatus, int $pIntId, string $pStrPhone = '') : bool {
        $sql = "UPDATE " . DB_BASE . ".usuarios_instancias SET `status` = ?, `telefone` = ? WHERE id = ?";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute([$pStrStatus, $pStrPhone, $pIntId]); // Substitua $id pelo valor do ID que você deseja atualizar
            return true;
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}