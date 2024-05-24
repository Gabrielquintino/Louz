<?php
namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use stdClass;

class AgendamentoModel extends DatabaseConfig
{
    public function save(array $arrData) : int {


        $sql = "INSERT INTO `".DB_USUARIO."`.`agendamentos` (`eventos_id`, `clientes_id`, `atendimentos_id`, `data`) VALUES (?, ?, ?, ?);";

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$arrData['eventos_id'], $arrData['clientes_id'], $arrData['atendimentos_id'], $arrData['data']]);
            return $this->getConnection()->lastInsertId();
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}