<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use stdClass;

class ClienteModel extends DatabaseConfig
{
    public function list(array $arrFilter = []) : array {
        $sql = "SELECT * FROM " . DB_USUARIO . ".clientes";
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
            $sql = "UPDATE " . DB_USUARIO . ".clientes SET $setClause WHERE id = ?";
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
            $strEmail = isset($arrData['email']) ? mb_substr($arrData['email'], 0, 150) : null;
            $strTelefone = isset($arrData['telefone']) ? mb_substr($arrData['telefone'], 0, 15) : null;
            $strCpf = isset($arrData['cpf']) ? mb_substr($arrData['cpf'], 0, 15): null;
            $strCnpj = isset($arrData['cnpj']) ? mb_substr($arrData['cnpj'], 0, 15) : null;
            $strTags = isset($arrData['tags']) ? ($arrData['tags']) : null;

            $sql = "INSERT INTO " . DB_USUARIO . ".clientes (`nome`, `email`, `telefone`, `cpf`, `cnpj`, `tags` ) VALUES (?, ?, ?, ?, ?, ?)";
            $pdo = $this->getConnection()->prepare($sql);

            try {
                $pdo->execute([$strNome, $strEmail, $strTelefone, $strCpf, $strCnpj, $strTags]);
                return $this->getConnection()->lastInsertId();
            } catch (Exception $err) {
                throw new Exception($err);
            }

        }
    }

    public function getClient(string $strFilterType, string $strFilterValue) : array {

        $sql = "SELECT * FROM " . DB_USUARIO . ".clientes WHERE " .$strFilterType. " = :valor";
        $pdo = $this->getConnection()->prepare($sql);
        $pdo->bindParam(':valor', $strFilterValue);

        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

    public function delete(int $intId) {
        $sql = "DELETE FROM " . DB_USUARIO . ".avaliacoes WHERE clientes_id = ?";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$intId]); // Substitua $id pelo valor do ID que você deseja excluir
        } catch (Exception $err) {
            throw new Exception($err);
        }  

        $sql = "DELETE FROM " . DB_USUARIO . ".atendimentos WHERE cliente_id = ?";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$intId]); // Substitua $id pelo valor do ID que você deseja excluir
        } catch (Exception $err) {
            throw new Exception($err);
        }  

        $sql = "DELETE FROM " . DB_USUARIO . ".clientes WHERE id = ?";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$intId]); // Substitua $id pelo valor do ID que você deseja excluir
            return ['success' => true, 'id' => $intId];
        } catch (Exception $err) {
            throw new Exception($err);
        }   
    }
}