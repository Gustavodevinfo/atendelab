<?php

class FrontendController
{
    public function dashboard(): void
    {
        $usuario = usuarioAtual();

        require __DIR__ . '/../Views/dashboard/index.php';
    }

    public function pessoas(): void
    {
        $usuario = usuarioAtual();

        require __DIR__ . '/../Views/pessoas/index.php';
    }

    public function tipos(): void
    {
        $usuario = usuarioAtual();

        require __DIR__ . '/../Views/tipos-atendimentos/index.php';
    }

    public function atendimentos(): void
    {
        $usuario = usuarioAtual();

        require __DIR__ . '/../Views/atendimentos/index.php';
    }
}