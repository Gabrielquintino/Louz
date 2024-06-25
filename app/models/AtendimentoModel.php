<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use DateTime;
use Exception;
use PDO;
use PDOException;
use stdClass;

class AtendimentoModel extends DatabaseConfig
{
    public function listagem() : array {
        $sql = "SELECT a.id, cl.id as clientId, cl.nome as cliente, cl.telefone, ch.nome as chatbot, fu.nome as funcionario, a.data_inicio `data` , a.mensagem, a.status
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
        $sql = "SELECT * FROM " . DB_USUARIO . ".atendimentos WHERE " . $strFilterType . " ORDER BY id DESC LIMIT 1";
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

    public function save(array $arrData) {

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
            try {
                // Obtenha a conexão com o banco de dados
                $pdo = $this->getConnection();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita o modo de erro do PDO
            
                // Prepare and execute the SQL statement
                $sql = "INSERT INTO " . DB_USUARIO . ".atendimentos (`chatbot_id`, `cliente_id`, `funcionarios_id`, `mensagem`, `index`, `status` ) VALUES (?, ?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE
                `mensagem` = VALUES(`mensagem`), 
                `status` = VALUES(`status`),
                `index` = VALUES(`index`)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$arrData['chatbot_id'], $arrData['cliente_id'], $arrData['funcionarios_id'], $arrData['mensagem'], $arrData['index'], $arrData['status']]);
            
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
    
    public function noPeriodo(string $pStrData) {

        function inicioDoTrimestre() {
            $mesAtual = (int)date('n');
            $trimestre = ceil($mesAtual / 3);
            $primeiroMesDoTrimestre = ($trimestre - 1) * 3 + 1;
            return (new DateTime("first day of " . DateTime::createFromFormat('m', $primeiroMesDoTrimestre)->format('F')))->setTime(0, 0)->getTimestamp();
        }
        
        switch ($pStrData) {
            case 'hoje':
                $data = (new DateTime('today'))->setTime(0, 0)->getTimestamp();
                break;
            case 'ultimosSeteDias':
                $data = (new DateTime('today'))->modify('-7 days')->setTime(0, 0)->getTimestamp();
                break;
            case 'esseMes':
                $data = (new DateTime('first day of this month'))->setTime(0, 0)->getTimestamp();
                break;
            case 'trimestral':
                $data = inicioDoTrimestre();
                break;
            default:
                $data = (new DateTime('today'))->setTime(0, 0)->getTimestamp();
                break;
        }
        
        $strSqlFilter = "'" . date('Y-m-d H:i:s', $data) . "'";

        $sql = "SELECT 
                (SELECT COUNT(*) FROM " . DB_USUARIO . ".atendimentos WHERE data_inicio >= ". $strSqlFilter . ") qt_total,
                (SELECT COUNT(*) FROM " . DB_USUARIO . ".atendimentos WHERE status = 'andamento' and data_inicio >= ". $strSqlFilter . ") qt_andamento,
                (SELECT COUNT(*) FROM " . DB_USUARIO . ".atendimentos WHERE status = 'espera' and data_inicio >= ". $strSqlFilter . ") qt_espera,
                (SELECT COUNT(*) FROM " . DB_USUARIO . ".atendimentos WHERE status = 'encerrado' and data_inicio >= ". $strSqlFilter . ") qt_encerrado,
                (SELECT COUNT(*) FROM " . DB_USUARIO . ".vendas WHERE status = 'concluida' and data >= ". $strSqlFilter . ") qt_vendas,
                (SELECT SUM(total) FROM " . DB_USUARIO . ".vendas WHERE status = 'concluida' and data >= ". $strSqlFilter . ") faturamento,
                (SELECT ROUND(AVG(total), 2) FROM " . DB_USUARIO . ".vendas WHERE status = 'concluida' and data >= ". $strSqlFilter . ") ticket_medio,
                (SELECT ROUND(AVG(nota), 2) FROM " . DB_USUARIO . ".avaliacoes WHERE data >= ". $strSqlFilter . ") avg_avaliacao,
                ROUND(AVG(TIMESTAMPDIFF(minute, data_inicio, data_ultima_mensagem)), 2) avg_media,
                ROUND(AVG(TIMESTAMPDIFF(minute, data_ultima_mensagem, NOW())), 2) avg_espera,
                (SELECT CONCAT(f.nome, ' ', ROUND(SUM(v.total), 2), '') AS melhor_funcionario
                    FROM ".DB_USUARIO.".vendas v
                    INNER JOIN ".DB_USUARIO.".funcionarios f ON f.id = v.funcionario_id
                    WHERE v.data >= ". $strSqlFilter . "
                    GROUP BY f.nome
                    ORDER BY SUM(v.total) DESC
                LIMIT 1) AS melhor_funcionario,
                (SELECT CONCAT(p.nome, ' ', ROUND(SUM(v.total), 2), '') AS produto_mais_vendido
                    FROM ".DB_USUARIO.".vendas v
                    INNER JOIN ".DB_USUARIO.".produtos p ON p.id = v.produtos_id
                    WHERE v.data >= ". $strSqlFilter . "
                    GROUP BY p.nome
                    ORDER BY SUM(v.total) DESC
                LIMIT 1) AS produto_mais_vendido                
                FROM " . DB_USUARIO . ".atendimentos;";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }
}