<?php

class AtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    private function usuarioResponsavel(): int
    {
        if (isset($_SESSION['usuario']['id'])) {
            return (int) $_SESSION['usuario']['id'];
        }

        $usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);

        if (!$usuarioId) {
            http_response_code(400);
            echo json_encode(['erro' => 'usuario_id é obrigatório.']);
            exit;
        }

        return $usuarioId;
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = 'SELECT
                    a.id,
                    a.pessoa_id,
                    p.nome AS pessoa_nome,
                    a.tipo_atendimento_id,
                    t.nome AS tipo_atendimento_nome,
                    a.usuario_id,
                    u.nome AS usuario_nome,
                    a.descricao,
                    a.status,
                    a.data_atendimento,
                    a.horario_atendimento,
                    a.observacao_final,
                    a.criado_em,
                    a.atualizado_em
                FROM atendimentos a
                INNER JOIN pessoas p ON p.id = a.pessoa_id
                INNER JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
                INNER JOIN usuarios u ON u.id = a.usuario_id
                ORDER BY a.id DESC';

        $stmt = $this->pdo->query($sql);
        $atendimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function buscarPorId(): void
    {
        $this->visualizar();
    }

    public function visualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $sql = 'SELECT
                    a.*,
                    p.nome AS pessoa_nome,
                    t.nome AS tipo_atendimento_nome,
                    u.nome AS usuario_nome
                FROM atendimentos a
                INNER JOIN pessoas p ON p.id = a.pessoa_id
                INNER JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
                INNER JOIN usuarios u ON u.id = a.usuario_id
                WHERE a.id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento não encontrado.']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoAtendimentoId = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $usuarioId = $this->usuarioResponsavel();

        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'aberto';
        $dataAtendimento = $_POST['data_atendimento'] ?? '';
        $horarioAtendimento = $_POST['horario_atendimento'] ?? '';
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');

        if (
            !$pessoaId ||
            !$tipoAtendimentoId ||
            !$usuarioId ||
            $descricao === '' ||
            $dataAtendimento === '' ||
            $horarioAtendimento === ''
        ) {
            http_response_code(400);

            echo json_encode([
                'erro' => 'Pessoa, tipo, usuário, descrição, data e horário são obrigatórios.'
            ]);

            return;
        }

        try {
            $sql = 'INSERT INTO atendimentos
                    (
                        pessoa_id,
                        tipo_atendimento_id,
                        usuario_id,
                        descricao,
                        status,
                        data_atendimento,
                        horario_atendimento,
                        observacao_final
                    )
                    VALUES
                    (
                        :pessoa_id,
                        :tipo_atendimento_id,
                        :usuario_id,
                        :descricao,
                        :status,
                        :data_atendimento,
                        :horario_atendimento,
                        :observacao_final
                    )';

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':pessoa_id', $pessoaId);
            $stmt->bindValue(':tipo_atendimento_id', $tipoAtendimentoId);
            $stmt->bindValue(':usuario_id', $usuarioId);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':data_atendimento', $dataAtendimento);
            $stmt->bindValue(':horario_atendimento', $horarioAtendimento);
            $stmt->bindValue(':observacao_final', $observacaoFinal ?: null);

            $stmt->execute();

            http_response_code(201);

            echo json_encode([
                'mensagem' => 'Atendimento cadastrado com sucesso.',
                'id' => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                'erro' => 'Erro ao cadastrar atendimento.'
            ]);
        }
    }

    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $pessoaId = filter_input(INPUT_POST, 'pessoa_id', FILTER_VALIDATE_INT);
        $tipoAtendimentoId = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $usuarioId = $this->usuarioResponsavel();

        $descricao = trim($_POST['descricao'] ?? '');
        $status = $_POST['status'] ?? 'aberto';
        $dataAtendimento = $_POST['data_atendimento'] ?? '';
        $horarioAtendimento = $_POST['horario_atendimento'] ?? '';
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');

        if (!$id || !$pessoaId || !$tipoAtendimentoId || !$usuarioId) {
            http_response_code(400);

            echo json_encode([
                'erro' => 'Dados obrigatórios não informados.'
            ]);

            return;
        }

        try {
            $sql = 'UPDATE atendimentos
                    SET
                        pessoa_id = :pessoa_id,
                        tipo_atendimento_id = :tipo_atendimento_id,
                        usuario_id = :usuario_id,
                        descricao = :descricao,
                        status = :status,
                        data_atendimento = :data_atendimento,
                        horario_atendimento = :horario_atendimento,
                        observacao_final = :observacao_final
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':pessoa_id', $pessoaId);
            $stmt->bindValue(':tipo_atendimento_id', $tipoAtendimentoId);
            $stmt->bindValue(':usuario_id', $usuarioId);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':data_atendimento', $dataAtendimento);
            $stmt->bindValue(':horario_atendimento', $horarioAtendimento);
            $stmt->bindValue(':observacao_final', $observacaoFinal ?: null);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Atendimento atualizado com sucesso.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                'erro' => 'Erro ao atualizar atendimento.'
            ]);
        }
    }

    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        $observacaoFinal = trim($_POST['observacao_final'] ?? '');

        if (!$id || !in_array($status, ['aberto', 'em_andamento', 'concluido'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Dados inválidos para alteração de status.']);
            return;
        }

        if ($status === 'concluido' && $observacaoFinal === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Observação final é obrigatória ao concluir.']);
            return;
        }

        try {
            $sql = 'UPDATE atendimentos
                    SET
                        status = :status,
                        observacao_final = :observacao_final
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);

            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':observacao_final', $observacaoFinal ?: null);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Status atualizado com sucesso.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                'erro' => 'Erro ao atualizar status do atendimento.'
            ]);
        }
    }

    public function alterarStatus(): void
    {
        $this->atualizarStatus();
    }

    public function opcoesFormulario(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $pessoas = $this->pdo
                ->query("SELECT id, nome FROM pessoas WHERE status = 'ativo' ORDER BY nome")
                ->fetchAll(PDO::FETCH_ASSOC);

            $tipos = $this->pdo
                ->query("SELECT id, nome FROM tipos_atendimentos WHERE status = 'ativo' ORDER BY nome")
                ->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'pessoas' => $pessoas,
                'tipos' => $tipos
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                'erro' => 'Erro ao carregar opções do formulário.'
            ]);
        }
    }

    public function excluir(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $sql = 'DELETE FROM atendimentos WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode([
                'mensagem' => 'Atendimento excluído com sucesso.'
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);

            echo json_encode([
                'erro' => 'Erro ao excluir atendimento.'
            ]);
        }
    }
}