<?php

require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';
require_once __DIR__ . '/app/Controllers/FrontendController.php';

require_once __DIR__ . '/app/Middleware/auth.php';

function responderRotaNaoEncontrada(string $mensagem): void
{
    http_response_code(404);
    echo $mensagem;
}

$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

switch ($controller) {

    case 'auth':
        $authController = new AuthController();

        switch ($action) {
            case 'login':
                $authController->exibirLogin();
                break;

            case 'entrar':
                $authController->entrar();
                break;

            case 'dashboard':
                $authController->dashboard();
                break;

            case 'logout':
                $authController->logout();
                break;

            default:
                responderRotaNaoEncontrada('Ação de autenticação não encontrada.');
        }
        break;

    case 'frontend':
        exigirAutenticacao();

        $frontendController = new FrontendController();

        switch ($action) {
            case 'dashboard':
                $frontendController->dashboard();
                break;

            case 'pessoas':
                $frontendController->pessoas();
                break;

            case 'tipos':
                $frontendController->tipos();
                break;

            case 'atendimentos':
                $frontendController->atendimentos();
                break;

            default:
                responderRotaNaoEncontrada('Ação de frontend não encontrada.');
        }
        break;

    case 'usuarios':
        exigirAutenticacao();

        $usuariosController = new UsuariosController();

        switch ($action) {
            case 'listar':
                $usuariosController->listar();
                break;

            case 'buscarPorId':
                $usuariosController->buscarPorId();
                break;

            case 'criar':
                $usuariosController->criar();
                break;

            case 'atualizar':
                $usuariosController->atualizar();
                break;

            case 'excluir':
                $usuariosController->excluir();
                break;

            default:
                responderRotaNaoEncontrada('Ação de usuários não encontrada.');
        }
        break;

    case 'pessoas':
        exigirAutenticacao();

        $pessoasController = new PessoasController();

        switch ($action) {
            case 'listar':
                $pessoasController->listar();
                break;

            case 'buscarPorId':
            case 'buscar':
                $pessoasController->buscarPorId();
                break;

            case 'criar':
                $pessoasController->criar();
                break;

            case 'atualizar':
                $pessoasController->atualizar();
                break;

            case 'inativar':
                $pessoasController->inativar();
                break;

            case 'excluir':
                $pessoasController->excluir();
                break;

            default:
                responderRotaNaoEncontrada('Ação de pessoas não encontrada.');
        }
        break;

    case 'tipos':
    case 'tiposAtendimentos':
        exigirAutenticacao();

        $tiposController = new TiposAtendimentosController();

        switch ($action) {
            case 'listar':
                $tiposController->listar();
                break;

            case 'buscarPorId':
            case 'buscar':
                $tiposController->buscarPorId();
                break;

            case 'criar':
                $tiposController->criar();
                break;

            case 'atualizar':
                $tiposController->atualizar();
                break;

            case 'inativar':
                $tiposController->inativar();
                break;

            case 'excluir':
                $tiposController->inativar();
                break;

            default:
                responderRotaNaoEncontrada('Ação de tipos de atendimento não encontrada.');
        }
        break;

    case 'atendimentos':
        exigirAutenticacao();

        $atendimentosController = new AtendimentosController();

        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;

            case 'buscarPorId':
            case 'buscar':
            case 'visualizar':
                $atendimentosController->visualizar();
                break;

            case 'criar':
                $atendimentosController->criar();
                break;

            case 'alterarStatus':
            case 'atualizarStatus':
                $atendimentosController->atualizarStatus();
                break;

            case 'opcoesFormulario':
                $atendimentosController->opcoesFormulario();
                break;

            default:
                responderRotaNaoEncontrada('Ação de atendimentos não encontrada.');
        }
        break;

    default:
        responderRotaNaoEncontrada('Controller não encontrado.');
}