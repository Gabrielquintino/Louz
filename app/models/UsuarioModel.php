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

            $this->createDatabaseAndTable($codigo, $email, $senha, $nome);

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
    public function createDatabaseAndTable($codigo, $email, $senha, $nome)
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
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

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

                CREATE TABLE IF NOT EXISTS `produtos` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `fornecedores_id` INT(11) NULL DEFAULT NULL,
                    `nome` VARCHAR(200) NOT NULL,
                    `valor` DECIMAL(10,2) NULL DEFAULT NULL,
                    `descricao` MEDIUMTEXT NULL DEFAULT NULL,
                    `dt_criacao` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    `dt_atualizacao` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `status` ENUM('rascunho', 'publicado') NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    INDEX `fk_produtos_fornecedores1_idx` (`fornecedores_id` ASC) VISIBLE,
                    CONSTRAINT `fk_produtos_fornecedores1`
                      FOREIGN KEY (`fornecedores_id`)
                      REFERENCES `fornecedores` (`id`)
                      ON DELETE NO ACTION
                      ON UPDATE NO ACTION 
                ) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

                CREATE TABLE `eventos` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `nome` varchar(150) NOT NULL,
                    `data_inicio` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    `data_fim` timestamp NULL DEFAULT NULL,
                    `periodicidade` int DEFAULT NULL,
                    `status` enum('ativo','inativo') DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

                CREATE TABLE `agendamentos` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `eventos_id` int NOT NULL,
                    `clientes_id` int NOT NULL,
                    `atendimentos_id` int NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `fk_agendamentos_eventos1_idx` (`eventos_id`),
                    KEY `fk_agendamentos_clientes1_idx` (`clientes_id`),
                    KEY `fk_agendamentos_atendimentos1_idx` (`atendimentos_id`),
                    CONSTRAINT `fk_agendamentos_atendimentos1` FOREIGN KEY (`atendimentos_id`) REFERENCES `atendimentos` (`id`),
                    CONSTRAINT `fk_agendamentos_clientes1` FOREIGN KEY (`clientes_id`) REFERENCES `clientes` (`id`),
                    CONSTRAINT `fk_agendamentos_eventos1` FOREIGN KEY (`eventos_id`) REFERENCES `eventos` (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

                CREATE TABLE IF NOT EXISTS `vendas` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `produtos_id` INT(11) NOT NULL,
                    `pagamento` ENUM('credito', 'debito', 'dinheiro', 'pix') NULL DEFAULT NULL,
                    `condicao` INT(2) NULL DEFAULT NULL,
                    `total` DECIMAL(10,2) NULL DEFAULT NULL,
                    `desconto` DECIMAL(10,2) NULL DEFAULT NULL,
                    `qtd_itens` INT(2) NULL DEFAULT NULL,
                    `data` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    `status` ENUM('concluida', 'estornada', 'aguardando_pagamento', 'chargeback') NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    INDEX `fk_vendas_produtos1_idx` (`produtos_id` ASC) VISIBLE,
                    CONSTRAINT `fk_vendas_produtos1`
                      FOREIGN KEY (`produtos_id`)
                      REFERENCES `produtos` (`id`)
                      ON DELETE NO ACTION
                      ON UPDATE NO ACTION 
                ) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
                  
                CREATE TABLE IF NOT EXISTS `avaliacoes` (
                    `clientes_id` INT(11) NOT NULL,
                    `atendimentos_id` INT(11) NOT NULL, -- Alterado para NOT NULL
                    `vendas_id` INT(11) NULL DEFAULT NULL,
                    `nota` DECIMAL(4,2) NULL DEFAULT NULL,
                    `data` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX `fk_avaliacoes_clientes1_idx` (`clientes_id` ASC) VISIBLE,
                    INDEX `fk_avaliacoes_vendas1_idx` (`vendas_id` ASC) VISIBLE,
                    CONSTRAINT `fk_avaliacoes_clientes1`
                      FOREIGN KEY (`clientes_id`)
                      REFERENCES `clientes` (`id`)
                      ON DELETE NO ACTION
                      ON UPDATE NO ACTION,
                    CONSTRAINT `fk_avaliacoes_atendimentos1`
                      FOREIGN KEY (`atendimentos_id`)
                      REFERENCES `atendimentos` (`id`)
                      ON DELETE NO ACTION
                      ON UPDATE NO ACTION,
                    CONSTRAINT `fk_avaliacoes_vendas1`
                      FOREIGN KEY (`vendas_id`)
                      REFERENCES `vendas` (`id`)
                      ON DELETE NO ACTION
                      ON UPDATE NO ACTION,
                    PRIMARY KEY (`clientes_id`, `atendimentos_id`) -- Removi `vendas_id` da chave primária
                ) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

                CREATE TABLE `cargos` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `nome` varchar(150) NOT NULL,
                    `status` enum('ativo','inativo') DEFAULT 'ativo',
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;

                INSERT INTO ".$databaseName.".`cargos` (`nome`,`status`)VALUES('admin','ativo');

                CREATE TABLE `funcionarios` (
                    `id` int NOT NULL AUTO_INCREMENT,
                    `cargos_id` int NOT NULL,
                    `nome` varchar(150) NOT NULL,
                    `email` varchar(150) NOT NULL,
                    `comissao` decimal(10,2) DEFAULT NULL,
                    `senha` varchar(60) NOT NULL,
                    `status` enum('ativo','inativo') DEFAULT 'ativo',
                    PRIMARY KEY (`id`),
                    KEY `fk_funcionarios_cargos1_idx` (`cargos_id`),
                    CONSTRAINT `fk_funcionarios_cargos1` FOREIGN KEY (`cargos_id`) REFERENCES `cargos` (`id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3;

                INSERT INTO ".$databaseName.".`funcionarios` (`cargos_id`, `nome`, `email`, `senha`, `status`) VALUES ('1', ".$nome.", ".$email.", ".$senha.", 'ativo');

            ";
            $pdo->exec($sqlTable);

            return true;
        } catch (\PDOException $e) {
            die("Erro ao criar base de dados: " . $e->getMessage());
        }
    }

    public function verificarEtapas(int $pUsuarioId) {
        $sql = "SELECT 
        (SELECT COUNT(*) FROM ".DB_BASE.".usuarios_instancias WHERE usuario_id = 7) instancia,
        (SELECT COUNT(*) FROM ".DB_USUARIO.".chatbot WHERE status = 'ativo' LIMIT 1) chatbot,
        (SELECT COUNT(*) FROM ".DB_USUARIO.".etapas WHERE status = 'ativo' LIMIT 1) etapas,
        (SELECT COUNT(*) FROM ".DB_USUARIO.".funcionarios WHERE status = 'ativo' LIMIT 1) funcionarios,
        (SELECT COUNT(*) FROM ".DB_USUARIO.".eventos WHERE status = 'ativo' LIMIT 1) eventos,
        (SELECT COUNT(*) FROM ".DB_USUARIO.".produtos WHERE status = 'ativo' LIMIT 1) produtos";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }
}
