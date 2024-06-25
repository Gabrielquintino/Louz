<?php
namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use DateTime;
use Exception;
use PDO;
use PDOException;
use stdClass;

class ProdutosModel extends DatabaseConfig
{

    public function listagem() {
        $sql = "SELECT * FROM `".DB_USUARIO."`.produtos";
        $pdo = $this->getConnection()->prepare($sql);
    
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

    public function save(array $arrData) : int {

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
            $sql = "UPDATE " . DB_USUARIO . ".produtos SET $setClause WHERE id = ?";
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

            try {
                // Obtenha a conexão com o banco de dados
                $pdo = $this->getConnection();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita o modo de erro do PDO
            
                // Sanitize and set variables
                $strNome = isset($arrData['nome']) ? mb_substr($arrData['nome'], 0, 200) : null;
                $strDescricao = isset($arrData['descricao']) ? $arrData['descricao'] : null;
                $decValor = $arrData['valor'];

                // Prepare and execute the SQL statement
                $sql = "INSERT INTO " . DB_USUARIO . ".produtos (`nome`, `descricao`, `valor`) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$strNome, $strDescricao, $decValor]);
            
                // Return the last inserted ID
                return $pdo->lastInsertId();
            
            } catch (PDOException $e) {
                // Log the error and throw an exception
                error_log("Database error: " . $e->getMessage());
                throw new Exception("Database error: " . $e->getMessage());
            } catch (Exception $err) {
                // Log the error and throw an exception
                error_log("General error: " . $err->getMessage());
                throw new Exception("General error: " . $err->getMessage());
            }

        }
    }

    public function get($intId) {

        $sql = "SELECT * FROM " . DB_USUARIO . ".produtos WHERE id = ". $intId;

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute();
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

    public function delete($intId) {
        $sql = "DELETE FROM ".DB_USUARIO.".produtos WHERE id = ?;";

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$intId]);
            return ['success' => true, 'id' => $intId];
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}