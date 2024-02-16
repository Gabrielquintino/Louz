<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use Exception;

class UsuarioModel extends DatabaseConfig {

    public function save($arrData) {

        print_r($arrData);

        $planoId = isset($arrData['planoId']) ? $arrData['planoId'] : 1;
        $url = $arrData['url'];
        $nome = $arrData['nome'];
        $email = $arrData['email'];
        $senha = md5($arrData['senha']);
        $tipo = 'pf';
        $phone = isset($arrData['phone']) ? $arrData['phone'] : null;

        $sql = "INSERT INTO " . DB_BASE . ".usuarios (`planos_id`, `email`, `senha`, `nome`, `cpf`, `cnpj`, `tipo`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$planoId, $email, $senha, $nome, null, null, $tipo]);
            return ['success' => true, 'id' => $this->getConnection()->lastInsertId()];
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}

