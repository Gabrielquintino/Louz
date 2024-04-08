<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class UtilController {
    
    
    public function generateRandomCode() {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = 8;
        $code = '';
        do {
            // Gerar um novo código
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[rand(0, strlen($characters) - 1)];
            }

            $usuarioModel = new UsuarioModel();
            $arrExiste = $usuarioModel->obterUsuarioPorCodigo($code);
            // Verificar se o código já existe na tabela de usuários
            $existingUser = $arrExiste['exist'];
        } while ($existingUser); // Continuar gerando um novo código até encontrar um único
        return $code;
    }
    
}