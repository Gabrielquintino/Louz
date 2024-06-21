<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use PDOException;
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

            try {
                // Obtenha a conexão com o banco de dados
                $pdo = $this->getConnection();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita o modo de erro do PDO
            
                // Sanitize and set variables
                $strNome = isset($arrData['nome']) ? mb_substr($arrData['nome'], 0, 150) : null;
                $strEmail = isset($arrData['email']) ? mb_substr($arrData['email'], 0, 150) : null;
                $strTelefone = isset($arrData['telefone']) ? mb_substr($arrData['telefone'], 0, 15) : null;
                $strCpf = isset($arrData['cpf']) ? mb_substr($arrData['cpf'], 0, 15) : null;
                $strCnpj = isset($arrData['cnpj']) ? mb_substr($arrData['cnpj'], 0, 15) : null;
                $strTags = isset($arrData['tags']) ? $arrData['tags'] : null;
            
                // Prepare and execute the SQL statement
                $sql = "INSERT INTO " . DB_USUARIO . ".clientes (`nome`, `email`, `telefone`, `cpf`, `cnpj`, `tags` ) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$strNome, $strEmail, $strTelefone, $strCpf, $strCnpj, $strTags]);
            
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

    public function adicionarTag($pClienteId, $pNovaTag) {
        // Seleciona as tags atuais
        $sql = "SELECT tags FROM " . DB_USUARIO . ".clientes WHERE id = :clienteId";
        $pdo = $this->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':clienteId', $pClienteId, PDO::PARAM_INT);
        $stmt->execute();
        
        // Busca as tags
        $tags = $stmt->fetchColumn();
    
        // Verifica se as tags são nulas e inicializa como uma string vazia se for o caso
        if ($tags === null) {
            $tags = '';
        }
    
        // Converte as tags para um array
        $tagsArray = $tags ? explode(', ', $tags) : [];
    
        // Adiciona a nova tag se ela ainda não existir
        if (!in_array($pNovaTag, $tagsArray)) {
            $tagsArray[] = $pNovaTag;
        }
    
        // Converte o array de volta para uma string
        $tagsAtualizadas = implode(', ', $tagsArray);
    
        // Atualiza o campo tags no banco de dados
        $sql = "UPDATE " . DB_USUARIO . ".clientes SET tags = :tags WHERE id = :clienteId";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':tags', $tagsAtualizadas, PDO::PARAM_STR);
        $stmt->bindParam(':clienteId', $pClienteId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    
    
}