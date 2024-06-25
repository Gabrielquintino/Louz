<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use DateTime;
use Exception;
use PDO;
use PDOException;
use stdClass;

class VendasModel extends DatabaseConfig
{
    public function listagem() : array {

        $strOrderBy = " ORDER BY v.data DESC ";

        $sql = "
            SELECT v.id, c.nome cliente, e.nome evento, p.nome produto, f.nome funcionario, v.pagamento, v.condicao, v.total, v.desconto, v.qtd_itens, v.data, v.status
            FROM ". DB_USUARIO .".vendas v
            INNER JOIN ". DB_USUARIO .".clientes c ON
            c.id = v.cliente_id
            LEFT JOIN ". DB_USUARIO .".eventos e ON
            e.id = v.evento_id
            LEFT JOIN ". DB_USUARIO .".produtos	p ON
            p.id = v.produtos_id
            LEFT JOIN ". DB_USUARIO .".funcionarios f ON
            f.id = v.funcionario_id " . $strOrderBy;
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }

    public function get($pIntId) : array {
        $sql = "SELECT * FROM ". DB_USUARIO .".vendas WHERE id = ?";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute([$pIntId]);
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;        
    }

    public function save(array $arrData, bool $booFormatDate = true) : int {
        try {
            // Verifica se a data deve ser formatada
            if ($booFormatDate) {
                $dataString = $arrData['data'];
                $timeData = DateTime::createFromFormat('d/m/Y H:i:s', $dataString);
                if (!$timeData) {
                    throw new Exception("Erro ao converter a data.");
                }
                // Obtém a data formatada no formato 'Y-m-d H:i:s'
                $formattedDate = $timeData->format('Y-m-d H:i:s');
            } else {
                $formattedDate = $arrData['data']; // Se não precisar formatar, assume-se que já está no formato correto
            }
    
            // Prepara os dados para inserção ou atualização
            if (isset($arrData['id']) && !empty($arrData['id'])) {
                // Atualização
                $intId = $arrData['id'];
                unset($arrData['id']);
                
                // Construindo a parte SET dinamicamente
                $setParts = [];
                foreach ($arrData as $key => $value) {
                    if ($key == "data") {
                        $arrData[$key] = $formattedDate;
                    }
                    if (!empty($value)) {
                        $setParts[] = "`$key` = ?";
                    } else {
                        unset($arrData[$key]);
                    }
                }
                $setClause = implode(", ", $setParts);
    
                // Construindo a query de UPDATE
                $sql = "UPDATE " . DB_USUARIO . ".vendas SET $setClause WHERE id = ?";
                $pdo = $this->getConnection()->prepare($sql);
    
                // Adicionando o id ao final do array de dados
                $arrData[] = $intId;
    
                // Executa a query de atualização
                $pdo->execute(array_values($arrData));
    
                return $intId;
    
            } else {
                // Inserção
                $pdo = $this->getConnection();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Habilita o modo de erro do PDO
                
                // Sanitize and set variables
                $intEventoId = isset($arrData['evento_id']) ? (!empty($arrData['evento_id']) ? $arrData['evento_id'] : null) : null;
                $intProdutoId = isset($arrData['produtos_id']) ? (!empty($arrData['produtos_id']) ? $arrData['produtos_id'] : null) : null;
                $intFuncionarioId = isset($arrData['funcionario_id']) ? $arrData['funcionario_id'] : null;
                $intAtendimentoId = isset($arrData['atendimento_id']) ? (!empty($arrData['atendimento_id']) ? $arrData['atendimento_id'] : null) : null;
                $intClienteId = isset($arrData['cliente_id']) ? $arrData['cliente_id'] : null;
                $strPagamento = $arrData['pagamento'];
                $intCondicao = isset($arrData['condicao']) ? $arrData['condicao'] : 1;
                $decTotal = $arrData['total'];
                $decDesconto = isset($arrData['desconto']) ? $arrData['desconto'] : 0.00; // Corrigido para 'desconto'
                $intQtdItens = isset($arrData['qtd_itens']) ? $arrData['qtd_itens'] : 1;
                $strStatus = isset($arrData['status']) ? $arrData['status'] : 'concluida';
    
                // Prepare and execute the SQL statement for insertion
                $sql = "INSERT INTO " . DB_USUARIO . ".vendas (
                        `evento_id`, `produtos_id`, `funcionario_id`, `atendimento_id`, `cliente_id`, `pagamento`,
                        `condicao`, `total`, `desconto`, `qtd_itens`, `data`, `status`
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $intEventoId, $intProdutoId, $intFuncionarioId, $intAtendimentoId, $intClienteId, 
                    $strPagamento, $intCondicao, $decTotal, $decDesconto, $intQtdItens, $formattedDate, $strStatus
                ]);
    
                // Return the last inserted ID
                return $pdo->lastInsertId();
    
            }
        } catch (PDOException $e) {
            // Log the database error and throw an exception
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage());
        } catch (Exception $err) {
            // Log the general error and throw an exception
            error_log("General error: " . $err->getMessage());
            throw new Exception("General error: " . $err->getMessage());
        }
    }
    
    

    public function delete($pIntId) : bool {
        $sql = "DELETE FROM " . DB_USUARIO . ".vendas WHERE id = ?";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$pIntId]); // Substitua $id pelo valor do ID que você deseja excluir
        } catch (Exception $err) {
            throw new Exception($err);
            return false;
        }
        
        return true;
    }

    public function noPeriodo(string $pPeriodo) {
        function inicioDoTrimestre() {
            $mesAtual = (int)date('n');
            $trimestre = ceil($mesAtual / 3);
            $primeiroMesDoTrimestre = ($trimestre - 1) * 3 + 1;
            return (new DateTime("first day of " . DateTime::createFromFormat('m', $primeiroMesDoTrimestre)->format('F')))->setTime(0, 0)->getTimestamp();
        }
        
        switch ($pPeriodo) {
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

        $sql = "SELECT DATE(data) AS venda_dia, SUM(total) AS total_vendas 
                FROM ". DB_USUARIO .".vendas 
                WHERE data >= " . $strSqlFilter . "
                GROUP BY venda_dia
                ORDER BY venda_dia";
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