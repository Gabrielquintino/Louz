<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;
use stdClass;

class ClienteModel extends DatabaseConfig
{
    public function list() : array {
        return [];
    }

    public function save(array $arrData) : int {
        $strNome = isset($arrData['nome']) ? mb_substr($arrData['nome'], 150) : null;
        $strEmail = isset($arrData['email']) ? mb_substr($arrData['email'], 0, 150) : null;
        $strTelefone = isset($arrData['telefone']) ? mb_substr($arrData['telefone'], 0, 15) : null;
        $strCpf = isset($arrData['cpf']) ? mb_substr($arrData['cpf'], 0, 15): null;
        $strCnpj = isset($arrData['cnpj']) ? mb_substr($arrData['cnpj'], 0, 15) : null;
        $strTags = isset($arrData['tags']) ? json_encode($arrData['tags']) : null;

        $sql = "INSERT INTO " . DB_USUARIO . ".clientes (`nome`, `email`, `telefone`, `cpf`, `cnpj`, `tags` ) VALUES (?, ?, ?, ?, ?, ?)";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$strNome, $strEmail, $strTelefone, $strCpf, $strCnpj, $strTags]);
            return $this->getConnection()->lastInsertId();
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

    public function getClient(string $strFilterType, string $strFilterValue) : array {

        $sql = "SELECT * FROM " . DB_USUARIO . ".clientes WHERE " .$strFilterType. " = :valor";
        $pdo = $this->getConnection()->prepare($sql);
        $pdo->bindParam(':valor', $strFilterValue);

        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}