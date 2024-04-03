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

    public function save($arrData) {

        $nome = $arrData['nome'];
        $phone = $arrData['phone'];
        $objJson = json_encode($arrData['objJson']);
        $id = $arrData['id'];

        if (isset($arrData['id'])) {
            $sql = "UPDATE " . $_SESSION["db_usuario"] . ".chatbot SET `nome` = ?, `integration_phone` = ?, `json` = ? WHERE id = ?";
            $pdo = $this->getConnection()->prepare($sql);
            
            try {
                $pdo->execute([$nome, $phone, $objJson, $id]); // Substitua $id pelo valor do ID que você deseja atualizar
                return ['success' => true, 'id' => $id];
            } catch (Exception $err) {
                throw new Exception($err);
            }
            
        } else {
            $sql = "INSERT INTO " . $_SESSION["db_usuario"] . ".chatbot (`nome`, `integration_phone`, `json`) VALUES (?, ?, ?)";
            $pdo = $this->getConnection()->prepare($sql);

            try {
                $pdo->execute([$nome, $phone, $objJson]);
                return ['success' => true, 'id' => $this->getConnection()->lastInsertId()];
            } catch (Exception $err) {
                throw new Exception($err);
            }
        }
    }

    public function getChatBot(int $intId) : array {
        $id = $intId;

        $sql = "SELECT * FROM " . $_SESSION["db_usuario"] . ".chatbot WHERE id = ?";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$id]); // Substitua $id pelo valor do ID que você deseja consultar
            $result = $pdo->fetch(PDO::FETCH_ASSOC);
            return $result; // Retorna os dados encontrados
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