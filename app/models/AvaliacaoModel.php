<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;

class AvaliacaoModel extends DatabaseConfig{


    public function listClientFeedbacks(int $pIntId) : array {
        $sql = "
        SELECT 
        c.id cliente_id, c.nome, a.nota,
        at.id as id_atendimento, ch.nome as chatbot_nome, a.data as data_avaliacao, at.data_inicio as data_atendimento, at.status as status_atendimento
        FROM db_femyap3b.avaliacoes a
        INNER JOIN db_femyap3b.clientes c ON
            c.id = a.clientes_id
        LEFT JOIN db_femyap3b.atendimentos at ON
            at.id = a.atendimentos_id
        LEFT JOIN db_femyap3b.chatbot ch ON
            ch.id = at.chatbot_id
        where a.clientes_id = :clientId
                   
        ";
        $pdo = $this->getConnection()->prepare($sql);
        $pdo->bindParam(':clientId', $pIntId);


        try {
            $pdo->execute(); 
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}