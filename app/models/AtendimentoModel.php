<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use stdClass;

class AtendimentoModel extends DatabaseConfig
{
    public function listagem() : array {
        $sql = "SELECT a.id, cl.id as clientId, cl.nome as cliente, cl.telefone, ch.nome as chatbot, fu.nome as funcionario, a.data, a.mensagem, a.status
        FROM " . $_SESSION['db_usuario'] . ".atendimentos a 
        INNER JOIN " . $_SESSION['db_usuario'] . ".chatbot ch ON
        ch.id = a.chatbot_id
        INNER JOIN " . $_SESSION['db_usuario'] . ".clientes cl ON 
        cl.id = a.cliente_id
        LEFT JOIN " . $_SESSION['db_usuario'] . ".funcionarios fu ON
        fu.id = a.funcionarios_id";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }

    public function getAtendimento(array $arrFilters, array $arrValues) : array {
        // Verificar se a quantidade de filtros e valores são iguais
        if (count($arrFilters) !== count($arrValues)) {
            throw new Exception("A quantidade de filtros e valores deve ser a mesma.");
        }
        
        // Construir a string de filtros dinamicamente
        $strFilters = [];
        foreach ($arrFilters as $filter) {
            $strFilters[] = $filter . " = :" . $filter;
        }
        $strFilterType = implode(" AND ", $strFilters);
        
        // Construir a query SQL
        $sql = "SELECT * FROM " . DB_USUARIO . ".atendimentos WHERE " . $strFilterType;
        $pdo = $this->getConnection()->prepare($sql);
        
        // Associar os valores dos filtros aos placeholders
        foreach ($arrFilters as $index => $filter) {
            $pdo->bindValue(':' . $filter, $arrValues[$index]);
        }
    
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $err) {
            throw new Exception($err->getMessage());
        }
    }

    public function save(array $arrData) : int {

        if (isset($arrData['id'])) {
            $intId = $arrData['id'];
            unset($arrData['id']);
            
            // Construindo a parte SET dinamicamente
            $setParts = [];
            foreach ($arrData as $key => $value) {
                $setParts[] = "`$key` = ?";
            }
            $setClause = implode(", ", $setParts);

            // Construindo a query de UPDATE
            $sql = "UPDATE " . DB_USUARIO . ".atendimentos SET $setClause WHERE id = ?";
            $pdo = $this->getConnection()->prepare($sql);

            // Adicionando o id ao final do array de dados
            $arrData[] = $intId;

            try {
                $pdo->execute(array_values($arrData)); // Certificando-se de usar os valores do array
                return ['success' => true, 'id' => $intId];
            } catch (Exception $err) {
                throw new Exception($err);
            }
        } else {
            $sql = "INSERT INTO " . DB_USUARIO . ".atendimentos (`chatbot_id`, `cliente_id`, `funcionarios_id`, `mensagem`, `index`, `status` ) VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE
            `mensagem` = VALUES(`mensagem`), 
            `status` = VALUES(`status`),
            `index` = VALUES(`index`)";
    
            $pdo = $this->getConnection()->prepare($sql);
    
            try {
                $pdo->execute([$arrData['chatbot_id'], $arrData['cliente_id'], $arrData['funcionarios_id'], $arrData['mensagem'], $arrData['index'], $arrData['status']]);
                return $this->getConnection()->lastInsertId();
            } catch (Exception $err) {
                throw new Exception($err);
            }
        }
    }
}