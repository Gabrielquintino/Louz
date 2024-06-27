<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use PDOException;

class EtapaModel extends DatabaseConfig
{

    public function list(): array
    {
        $sql = "SELECT *
        FROM " . DB_USUARIO . ".etapas e
        WHERE 1=1 and e.status = 'ativo' ORDER BY e.order, e.id DESC";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }

    public function log($pBoolOnlyActive = false)
    {
        $strSqlFilter = $pBoolOnlyActive ? "and c.status = 'ativo' " : "";

        $sql = "SELECT c.id, c.nome, c.email, c.telefone, c.tags, e.nome as etapa, e.order as etapa_order, l.data
        FROM " . DB_USUARIO . ".clientes c
        INNER JOIN " . DB_USUARIO . ".etapas_log l ON c.id = l.clientes_id
        INNER JOIN " . DB_USUARIO . ".etapas e ON l.etapas_id = e.id
        INNER JOIN (
            SELECT clientes_id, MAX(data) as max_data
            FROM " . DB_USUARIO . ".etapas_log
            GROUP BY clientes_id
        ) l_max ON l.clientes_id = l_max.clientes_id AND l.data = l_max.max_data
        ORDER BY l.data DESC;
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

    public function save($arrData)
    {
        $pdo = $this->getConnection();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    
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
            $stmt = $pdo->prepare($sql);
    
            // Adicionando o id ao final do array de dados
            $arrDataValues = array_values($arrData);
            $arrDataValues[] = $intId;
    
            try {
                $stmt->execute($arrDataValues); // Certificando-se de usar os valores do array
                return $intId;
            } catch (Exception $err) {
                http_response_code(500);
                throw new Exception($err->getMessage());
            }  
        } else {
    
            $sql = "INSERT INTO " . DB_USUARIO . ".etapas (`nome`, `chatbot_id`, `order`) VALUES (?, ?, ?)";
    
            try {
                // Obtenha a conexão com o banco de dados
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array_values($arrData));
    
                // Return the last inserted ID
                return $pdo->lastInsertId();
            } catch (PDOException $e) {
                // Log the error and throw an exception
                http_response_code(500);
                error_log("Database error: " . $e->getMessage());
                throw new Exception("Database error: " . $e->getMessage(), 1);
            } catch (Exception $err) {
                // Log the error and throw an exception
                http_response_code(500);
                error_log("General error: " . $err->getMessage());
                throw new Exception("General error: " . $err->getMessage());
            }
        }
    }
    

    public function saveLog($pClienteId, $pEtapaId)
    {
        $sql = "INSERT INTO " . DB_USUARIO . ".etapas_log (`clientes_id`, `etapas_id`) VALUES (?, ?)";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$pClienteId, $pEtapaId]);
            return $this->getConnection()->lastInsertId();
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

    public function delete($pEtapaId, $pNewEtapaId, $booTemCliente)
    {

        if ( $booTemCliente != "false") {
            // Primeiro SELECT para pegar os clientes_id
            $sql = "SELECT clientes_id FROM " . DB_USUARIO . ".etapas_log WHERE etapas_id = :etapaId GROUP BY clientes_id";
            $pdo = $this->getConnection()->prepare($sql);

            try {
                $pdo->execute([':etapaId' => $pEtapaId]);
                $arrClients = $pdo->fetchAll(PDO::FETCH_COLUMN, 0); // Usando PDO::FETCH_COLUMN para pegar apenas os valores

                // Construir a query de inserção com múltiplos valores
                $values = array_map(function ($clientId) use ($pNewEtapaId) {
                    return "($clientId, $pNewEtapaId)";
                }, $arrClients);
                $valuesString = implode(', ', $values);

                $sql = "INSERT INTO " . DB_USUARIO . ".etapas_log (`clientes_id`, `etapas_id`) VALUES $valuesString";
                $pdo = $this->getConnection()->prepare($sql);

                try {
                    $pdo->execute();
                } catch (Exception $err) {
                    throw new Exception("Erro ao inserir no log de etapas: " . $err->getMessage());
                }
            } catch (Exception $err) {
                throw new Exception("Erro ao buscar clientes: " . $err->getMessage());
            }
        }

        // Atualizando o status da etapa para 'inativo'
        $sql = "UPDATE " . DB_USUARIO . ".etapas SET status = 'inativo' WHERE id = :etapaId";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([':etapaId' => $pEtapaId]);
        } catch (Exception $err) {
            throw new Exception("Erro ao atualizar a etapa: " . $err->getMessage());
        }
    }
}
