<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use Exception;
use PDO;

class UsuarioModel extends DatabaseConfig {

    public function save($arrData) {

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

    public function login($usuario, $senha) : array {

        $sql = "SELECT * FROM " . DB_BASE . ".usuarios WHERE email = :email AND senha = :senha";
        $pdo = $this->getConnection()->prepare($sql);
        $senha_md5 = md5($senha); // Atribui o resultado de md5($senha) a uma variável
        $pdo->bindParam(':email', $usuario);
        $pdo->bindParam(':senha', $senha_md5); // Passa a variável para bindParam()
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return [
                'exist' => count($result) > 0,
                'data' => $result
            ];
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}

