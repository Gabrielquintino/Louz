<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use Exception;
use PDO;

class ChatBotModel extends DatabaseConfig {

    public function listagem() : array {

        if (!defined('DB_USUARIO')) {
            $sql = "SELECT * FROM " . DB_BASE . ".usuarios WHERE email = :email";
            $pdo = $this->getConnection()->prepare($sql);
            $pdo->bindParam(':email', $_SESSION['usuario']);
            $pdo->execute();
            $resultado = $pdo->fetchAll(PDO::FETCH_ASSOC);            

            define('DB_USUARIO', 'db_' . $resultado[0]["codigo"]);
        }

        $sql = "SELECT * FROM " . DB_USUARIO . ".chatbot";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }

    public function decompressJSON($compressedData) {
        $compressedData = base64_decode($compressedData);
        $jsonStringUtf8 = mb_convert_encoding($compressedData, 'UTF-8');
        return $jsonStringUtf8;
    }

    public function save($arrData) {

        $nome = $arrData['nome'];
        $phone = $arrData['phone'];
        $objJson = $this->decompressJSON($arrData['objJson']);
        $arrOrder = $this->decompressJSON($arrData['arrOrder']);
        $id = $arrData['id'];

        if (isset($arrData['id']) && !empty($arrData['id']) ) {
            $sql = "UPDATE " . $_SESSION["db_usuario"] . ".chatbot SET `nome` = ?, `integration_phone` = ?, `json` = ?, `arr_ordem` = ? WHERE id = ?";
            $pdo = $this->getConnection()->prepare($sql);
            
            try {
                $pdo->execute([$nome, $phone, $objJson, $arrOrder, $id]); // Substitua $id pelo valor do ID que você deseja atualizar
                return ['success' => true, 'id' => $id];
            } catch (Exception $err) {
                throw new Exception($err);
            }
            
        } else {
            $sql = "INSERT INTO " . $_SESSION["db_usuario"] . ".chatbot (`nome`, `integration_phone`, `json`, `arr_ordem`) VALUES (?, ?, ?, ?)";
            $pdo = $this->getConnection()->prepare($sql);

            try {
                $pdo->execute([$nome, $phone, $objJson, $arrOrder]);
                return ['success' => true, 'id' => $this->getConnection()->lastInsertId()];
            } catch (Exception $err) {
                throw new Exception($err);
            }
        }
    }

    public function getChatBot($intId, string $strUserDb = '') : array {
        $id = $intId;

        if (empty($strUserDb)) {
            $sql = "SELECT * FROM " . $_SESSION["db_usuario"] . ".chatbot WHERE id = ?";        
        } else {
            $sql = "SELECT * FROM " . $strUserDb . ".chatbot WHERE integration_phone = ?";
        }

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$id]); // Substitua $id pelo valor do ID que você deseja consultar
            $result = $pdo->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return $result; // Retorna os dados encontrados
            } else {
                return [];
            }
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

    public function delete(int $intId) : array {
        $sql = "DELETE FROM " . $_SESSION["db_usuario"] . ".chatbot WHERE id = ?";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute([$intId]); // Substitua $id pelo valor do ID que você deseja excluir
            return ['success' => true, 'id' => $intId];
        } catch (Exception $err) {
            throw new Exception($err);
        }        
    }

}