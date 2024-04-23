<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use Exception;
use PDO;

class UsuarioModel extends DatabaseConfig
{

    public function save($arrData)
    {

        $utilController = new UtilController();
        $codigo = $utilController->generateRandomCode();

        $planoId = isset($arrData['planoId']) ? $arrData['planoId'] : 1;
        $nome = $arrData['nome'] . ' ' . $arrData['sobreNome'];
        $email = $arrData['email'];
        $senha = md5($arrData['senha']);
        $tipo = $arrData['tipo'];
        $identidade = $arrData['identidade'];
        $phone = isset($arrData['phone']) ? $arrData['phone'] : null;

        $sql = "INSERT INTO " . DB_BASE . ".usuarios (`plano_id`, `codigo`, `email`, `senha`, `nome`, `cpf_cnpj`, `tipo`, `telefone`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$planoId, $codigo, $email, $senha, $nome, $identidade, $tipo, $phone]);

            $this->createDatabaseAndTable($codigo);

            return ['success' => true, 'id' => $this->getConnection()->lastInsertId()];
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

    public function login($usuario, $senha): array
    {

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

    public function obterUsuarioPorCodigo($codigo)
    {
        $sql = "SELECT * FROM " . DB_BASE . ".usuarios WHERE codigo = :codigo";
        $pdo = $this->getConnection()->prepare($sql);
        $pdo->bindParam(':codigo', $codigo);

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

    public function obterUsuarioPorId(int $pIntId)
    {
        $sql = "SELECT * FROM " . DB_BASE . ".usuarios WHERE id = :id";
        $pdo = $this->getConnection()->prepare($sql);
        $pdo->bindParam(':id', $pIntId);

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

    // Função para criar a nova base de dados e a tabela 'chatbot' usando o PDO
    public function createDatabaseAndTable($codigo)
    {
        try {
            // Obter a instância existente de PDO
            $pdo = $this->getConnection();
            
            // Nome da nova base de dados
            $databaseName = 'db_' . $codigo;

            // Criar a nova base de dados
            $sql = "CREATE DATABASE $databaseName";
            $pdo->exec($sql);

            // Selecionar a nova base de dados
            $pdo->exec("USE $databaseName");

            // Criar a tabela 'chatbot' dentro da nova base de dados
            $sqlTable = "
                CREATE TABLE `chatbot` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `nome` varchar(100) NOT NULL,
                    `integration_phone` varchar(12) NOT NULL,
                    `json` longtext NOT NULL,
                    `data` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `id_UNIQUE` (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb3;

                CREATE TABLE IF NOT EXISTS `atendimentos` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `chatbot_id` INT(11) NOT NULL,
                    `cliente_id` INT(11) NOT NULL,
                    `data` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(),
                    `mensagem` VARCHAR(200) NOT NULL,
                    `index` INT(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    INDEX `fk_atendimentos_clientes_idx` (`cliente_id` ASC) VISIBLE,
                    INDEX `fk_atendimentos_chatbot1_idx` (`chatbot_id` ASC) VISIBLE,
                    CONSTRAINT `fk_atendimentos_clientes`
                      FOREIGN KEY (`cliente_id`)
                      REFERENCES `clientes` (`id`)
                      ON DELETE NO ACTION
                      ON UPDATE NO ACTION,
                    CONSTRAINT `fk_atendimentos_chatbot1`
                      FOREIGN KEY (`chatbot_id`)
                      REFERENCES `chatbot` (`id`)
                      ON DELETE NO ACTION
                      ON UPDATE NO ACTION
                ) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
                  
                CREATE TABLE IF NOT EXISTS `clientes` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `nome` VARCHAR(150) NULL DEFAULT NULL,
                    `email` VARCHAR(150) NULL DEFAULT NULL,
                    `telefone` VARCHAR(15) NULL DEFAULT NULL,
                    `cpf` VARCHAR(15) NULL DEFAULT NULL,
                    `cnpj` VARCHAR(15) NULL DEFAULT NULL,
                    `tags` MEDIUMTEXT NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE INDEX `telefone_UNIQUE` (`telefone` ASC) VISIBLE,
                    UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE
                ) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
            ";
            $pdo->exec($sqlTable);

            return true;
        } catch (\PDOException $e) {
            die("Erro ao criar base de dados: " . $e->getMessage());
        }
    }
}
