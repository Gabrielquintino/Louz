<?php

namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use DateTime;
use Exception;
use PDO;
use stdClass;

class EventoModel extends DatabaseConfig
{
    public function listagem() : array {
        $sql = "SELECT * FROM ". DB_USUARIO .".eventos WHERE status = 'ativo'";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;
    }

    public function get($pIntId) : array {
        $sql = "SELECT * FROM ". DB_USUARIO .".eventos WHERE status = 'ativo' and id = ?";
        $pdo = $this->getConnection()->prepare($sql);
        
        try {
            $pdo->execute([$pIntId]);
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $err) {
            throw new Exception($err);
        }

        return $result;        
    }

    public function save($arrData) : int {

        if (isset($arrData['id']) && !empty($arrData['id'])) {

            $intId = $arrData['id'];
            unset($arrData['id']);
            
            // Construindo a parte SET dinamicamente
            $setParts = [];
            foreach ($arrData as $key => $value) {
                if ($key == 'data_inicio') {
                    if (DateTime::createFromFormat('d/m/Y H:i:s', $arrData['data_inicio']) !== false) {
                        $arrData['data_inicio'] = DateTime::createFromFormat('d/m/Y H:i:s', $arrData['data_inicio'])->format('Y-m-d H:i:s');
                    } else {
                        $arrData['data_inicio'] = null;   
                    }
                } elseif ($key == 'data_fim') {
                    if (DateTime::createFromFormat('d/m/Y H:i:s', $arrData['data_fim']) !== false) {
                        $arrData['data_fim'] = DateTime::createFromFormat('d/m/Y H:i:s', $arrData['data_fim'])->format('Y-m-d H:i:s');
                    } else {
                        $arrData['data_fim'] = null;   
                    }
                }

                $setParts[] = "`$key` = ?";
            }
            $setClause = implode(", ", $setParts);

            // Construindo a query de UPDATE
            $sql = "UPDATE " . DB_USUARIO . ".eventos SET $setClause WHERE id = ?";
            $pdo = $this->getConnection()->prepare($sql);

            // Adicionando o id ao final do array de dados
            $arrData[] = $intId;

            try {
                $pdo->execute(array_values($arrData)); // Certificando-se de usar os valores do array
                return $intId;
            } catch (Exception $err) {
                throw new Exception($err);
            }  
        } else {

            $strNome = isset($arrData['nome']) ? mb_substr($arrData['nome'],0, 150) : null;
            $dtInicio = isset($arrData['data_inicio']) ? DateTime::createFromFormat('d/m/Y H:i:s', $arrData['data_inicio'])->format('Y-m-d H:i:s') : null;
            $dtFim = isset($arrData['data_fim']) ? DateTime::createFromFormat('d/m/Y H:i:s', $arrData['data_fim'])->format('Y-m-d H:i:s') : null;
            $intPeriodicidade = isset($arrData['periodicidade']) ? (int) $arrData['periodicidade'] : null;
            $strStatus = isset($arrData['status']) ? $arrData['status'] : 'ativo';

            $sql = "INSERT INTO " . DB_USUARIO . ".eventos (`nome`, `data_inicio`, `data_fim`, `periodicidade`, `status` ) VALUES (?, ?, ?, ?, ?)";
            $pdo = $this->getConnection()->prepare($sql);

            try {
                $pdo->execute([$strNome, $dtInicio, $dtFim, $intPeriodicidade, $strStatus]);
                return $this->getConnection()->lastInsertId();
            } catch (Exception $err) {
                throw new Exception($err);
            }

        }


        return [];
    }

    public function delete($pIntId) : bool {
        $sql = "DELETE FROM ".DB_USUARIO.".agendamentos WHERE eventos_id = ?";
        $pdo = $this->getConnection()->prepare($sql);
        try {
            $pdo->execute([$pIntId]);
        } catch (Exception $err) {
            throw new Exception($err);
            return false;
        }

        $sql = "DELETE FROM " . DB_USUARIO . ".eventos WHERE id = ?";
        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$pIntId]); // Substitua $id pelo valor do ID que você deseja excluir
        } catch (Exception $err) {
            throw new Exception($err);
            return false;
        }
        
        return true;
    }
}