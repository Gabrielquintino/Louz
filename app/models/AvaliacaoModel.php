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
        c.id cliente_id, c.nome, a.nota, v.id as id_venda, p.nome as produto, v.total as valor_venda, v.status as status_venda, 
        at.id as id_atendimento, ch.nome as chatbot_nome, a.data as data_avaliacao, at.data as data_atendimento, at.status as status_atendimento
        FROM db_femyap3b.avaliacoes a
        INNER JOIN db_femyap3b.clientes c ON
            c.id = a.clientes_id
        LEFT JOIN db_femyap3b.atendimentos at ON
            at.id = a.atendimentos_id
        LEFT JOIN db_femyap3b.chatbot ch ON
            ch.id = at.chatbot_id
        LEFT JOIN db_femyap3b.vendas v ON
            v.id = a.vendas_id
        LEFT JOIN db_femyap3b.produtos p ON
            p.id = v.produtos_id
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