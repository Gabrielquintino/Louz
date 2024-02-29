<?php

namespace App\Controllers;

class SessaoManager {
    public static $getUsuarioLogado;
    public static $getUsuario;

    public static function iniciarSessao() {
        session_start();
    }

    public function setUsuarioLogado($valor) {
        $_SESSION['usuario_logado'] = $valor;
    }

    public function setUsuario($valor) {
        $_SESSION['usuario'] = $valor;
    }

    public function getUsuarioLogado() {
        $this::$getUsuarioLogado = isset($_SESSION['usuario_logado']) ? $_SESSION['usuario_logado'] : false;
        return $this::$getUsuarioLogado;
    }

    public function getUsuario() {
        $this::$getUsuario = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
        return $this::$getUsuario;
    }
}
