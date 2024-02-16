<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use Exception;

class CloneModel extends DatabaseConfig {

    public function save($arrData) {

        print_r($arrData);

        $url = $arrData['url'];
        $name = $arrData['name'];
        $email = $arrData['email'];
        $phone = $arrData['phone'];

        $sql = "INSERT INTO " . DB_BASE . ".import (name, email, phone, url) VALUES (?, ?, ?, ?)";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$name, $email, $phone, $url]);
            return ['success' => true, 'id' => $this->getConnection()->lastInsertId()];
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}

