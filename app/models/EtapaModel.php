<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;

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

        $sql = "INSERT INTO " . DB_USUARIO . ".etapas (`chatbot_id`, `nome`, `order`) VALUES (?, ?, ?)";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$arrData['chatbot_id'], $arrData['nome'], $arrData['order']]);
            return $this->getConnection()->lastInsertId();
        } catch (Exception $err) {
            throw new Exception($err);
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
