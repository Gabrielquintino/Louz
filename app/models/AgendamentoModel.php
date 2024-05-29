<?php
namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use DateTime;
use Exception;
use PDO;
use stdClass;

class AgendamentoModel extends DatabaseConfig
{

    public function listagem() {
        $sql = "SELECT ag.id, ev.nome evento, cl.nome cliente, cl.telefone, ag.data
            FROM `".DB_USUARIO."`.agendamentos ag
            INNER JOIN `".DB_USUARIO."`.eventos ev ON
            ev.id = ag.eventos_id
            INNER JOIN `".DB_USUARIO."`.clientes cl ON
            cl.id = ag.clientes_id";

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


        // Cria um objeto DateTime a partir do formato específico
        $dateTime = DateTime::createFromFormat('d/m/Y H:i:s', $arrData['data']);

        // Verifica se a data foi convertida corretamente
        if ($dateTime === false) {
            throw new Exception("Erro ao converter a data");
        }

        $sql = "INSERT INTO `".DB_USUARIO."`.`agendamentos` (`eventos_id`, `clientes_id`, `atendimentos_id`, `data`) VALUES (?, ?, ?, ?);";

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$arrData['eventos_id'], $arrData['clientes_id'], $arrData['atendimentos_id'], $dateTime->format('Y-m-d H:i:s')]);
            return $this->getConnection()->lastInsertId();
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

    public function get($intId) {

        $sql = "SELECT ev.nome evento, cl.nome cliente, cl.telefone, fu.nome funcionario, at.mensagem, ag.data
        FROM `".DB_USUARIO."`.agendamentos ag
        INNER JOIN `".DB_USUARIO."`.eventos ev ON
        ev.id = ag.eventos_id
        INNER JOIN `".DB_USUARIO."`.clientes cl ON
        cl.id = ag.clientes_id
        INNER JOIN `".DB_USUARIO."`.atendimentos at ON
        at.id = ag.atendimentos_id
        LEFT JOIN `".DB_USUARIO."`.funcionarios fu ON
        fu.id = at.funcionarios_id
        WHERE ag.id ='".$intId."'";

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
        $sql = "DELETE FROM ".DB_USUARIO.".agendamentos WHERE id = ?;";

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$intId]);
            return ['success' => true, 'id' => $intId];
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}