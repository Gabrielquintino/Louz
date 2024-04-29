<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use stdClass;

class AtendimentoModel extends DatabaseConfig
{
    public function list() : array {
        return [];
    }

    public function getAtendimento(string $strFilterType, string $strFilterValue) : array {
        
        $sql = "SELECT * FROM " . DB_USUARIO . ".atendimentos WHERE " .$strFilterType. " = :valor";
        $pdo = $this->getConnection()->prepare($sql);
        $pdo->bindParam(':valor', $strFilterValue);

        try {
            $pdo->execute(); // Substitua $id pelo valor do ID que você deseja consultar
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                $result = $result[0];
            }
            return $result;
        } catch (Exception $err) {
            throw new Exception($err);
        }

    }

    public function save(array $arrData) : int {
        $sql = "INSERT INTO " . DB_USUARIO . ".atendimentos (`chatbot_id`, `cliente_id`, `mensagem`, `index`, `status` ) VALUES (?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE
        `mensagem` = VALUES(`mensagem`), 
        `status` = VALUES(`status`),
        `index` = VALUES(`index`)";

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$arrData['chatbot_id'], $arrData['cliente_id'], $arrData['mensagem'], $arrData['index'], $arrData['status']]);
            return $this->getConnection()->lastInsertId();
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}