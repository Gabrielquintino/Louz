<?php
namespace App\Models;

use App\Config\DatabaseConfig;
use App\Controllers\UtilController;
use DateTime;
use Exception;
use PDO;
use stdClass;

class ProdutosModel extends DatabaseConfig
{

    public function listagem() {
        $sql = "SELECT * FROM `".DB_USUARIO."`.produtos";
        $pdo = $this->getConnection()->prepare($sql);
    
        try {
            $pdo->execute();
            $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }

    public function save($arrData) : int {

        // Processar dias da semana
        $diasSemana = isset($arrData['dias_semana']) ? $arrData['dias_semana'] : [];
    
        // Processar horários da semana
        $horariosSemana = [];
        foreach ($diasSemana as $dia) {
            if (isset($arrData['horarios_semana'][$dia])) {
                $inicio = isset($arrData['horarios_semana'][$dia][0]) ? $arrData['horarios_semana'][$dia][0] : null;
                $fim = isset($arrData['horarios_semana'][$dia][1]) ? $arrData['horarios_semana'][$dia][1] : null;
                if ($inicio && $fim) {
                    $horariosSemana[ucfirst($dia)] = $inicio . '-' . $fim;
                } else {
                    $horariosSemana[ucfirst($dia)] = "-";
                }
            } else {
                $horariosSemana[ucfirst($dia)] = "-";
            }
        }
    
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
            $sql = "UPDATE " . DB_USUARIO . ".eventos SET $setClause, `duracao_horas` = ?, `dias_semana` = ?, `horarios_semana` = ? WHERE id = ?";
            $pdo = $this->getConnection()->prepare($sql);
    
            // Adicionando os novos campos e o id ao final do array de dados
            $arrData['duracao_horas'] = isset($arrData['duracao_horas']) ? (int) $arrData['duracao_horas'] : null;
            $arrData['dias_semana'] = implode(', ', $diasSemana);
            $arrData['horarios_semana'] = json_encode($horariosSemana);
            $arrData[] = $intId;
    
            try {
                $pdo->execute(array_values($arrData)); // Certificando-se de usar os valores do array
                return $intId;
            } catch (Exception $err) {
                throw new Exception($err);
            }  
        } else {
    
            $strNome = isset($arrData['nome']) ? mb_substr($arrData['nome'],0, 150) : null;
            $dtInicio = (isset($arrData['data_inicio']) && !empty($arrData['data_inicio'])) ? DateTime::createFromFormat('d/m/Y H:i:s', $arrData['data_inicio'])->format('Y-m-d H:i:s') : null;
            $dtFim = (isset($arrData['data_fim']) && !empty($arrData['data_fim'])) ? DateTime::createFromFormat('d/m/Y H:i:s', $arrData['data_fim'])->format('Y-m-d H:i:s') : null;
            $intPeriodicidade = isset($arrData['periodicidade']) ? (int) $arrData['periodicidade'] : null;
            $intDuracaoHoras = isset($arrData['duracao_horas']) ? (int) $arrData['duracao_horas'] : null;
            $strStatus = isset($arrData['status']) ? $arrData['status'] : 'ativo';
    
            $sql = "INSERT INTO " . DB_USUARIO . ".eventos (`nome`, `data_inicio`, `data_fim`, `periodicidade`, `status`, `duracao_horas`, `dias_semana`, `horarios_semana`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $pdo = $this->getConnection()->prepare($sql);
    
            try {
                $pdo->execute([$strNome, $dtInicio, $dtFim, $intPeriodicidade, $strStatus, $intDuracaoHoras, implode(', ', $diasSemana), json_encode($horariosSemana)]);
                return $this->getConnection()->lastInsertId();
            } catch (Exception $err) {
                throw new Exception($err);
            }
        }
    
        return [];
    }


    public function get($intId) {

        $sql = "SELECT * FROM " . DB_USUARIO . ".produtos WHERE id = ". $intId;

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute();
            $result = $pdo->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                return $result; // Retorna os dados encontrados
            } else {
                return [];
            }
        } catch (Exception $err) {
            throw new Exception($err);
        }        
    }

    public function delete($intId) {
        $sql = "DELETE FROM ".DB_USUARIO.".produtos WHERE id = ?;";

        $pdo = $this->getConnection()->prepare($sql);

        try {
            $pdo->execute([$intId]);
            return ['success' => true, 'id' => $intId];
        } catch (Exception $err) {
            throw new Exception($err);
        }
    }
}