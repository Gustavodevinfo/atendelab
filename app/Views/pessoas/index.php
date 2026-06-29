<!doctype html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pessoas - AtendeLab</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">AtendeLab</span>

        <a class="btn btn-outline-light btn-sm" href="?controller=auth&action=dashboard">
            Dashboard
        </a>
    </div>
</nav>

<div class="container mt-4">

    <div id="alerta"></div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Pessoas</h1>

        <button class="btn btn-primary btn-sm" onclick="abrirFormulario()">
            Nova pessoa
        </button>
    </div>

    <div class="card shadow-sm mb-4 d-none" id="cardFormulario">
        <div class="card-body">
            <h2 class="h5 mb-3" id="tituloFormulario">Cadastrar pessoa</h2>

            <form id="formPessoa">
                <input type="hidden" id="pessoaId" name="id">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Documento</label>
                        <input type="text" class="form-control" id="documento" name="documento" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Telefone</label>
                        <input type="text" class="form-control" id="telefone" name="telefone">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Curso</label>
                        <input type="text" class="form-control" id="curso" name="curso">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Período</label>
                        <input type="text" class="form-control" id="periodo" name="periodo">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Observações</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"></textarea>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">
                        Salvar
                    </button>

                    <button type="button" class="btn btn-secondary" onclick="fecharFormulario()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Documento</th>
                            <th>E-mail</th>
                            <th>Curso</th>
                            <th>Período</th>
                            <th>Status</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>

                    <tbody id="tabelaPessoas">
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                Carregando pessoas...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="assets/js/api.js"></script>

<script>
    const formPessoa = document.getElementById('formPessoa');

    async function carregarPessoas() {
        try {
            const dados = AtendeLabApi.toList(
                await AtendeLabApi.get('pessoas', 'listar')
            );

            const tbody = document.getElementById('tabelaPessoas');

            if (!dados.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4">Nenhuma pessoa cadastrada.</td></tr>';
                return;
            }

            tbody.innerHTML = dados.map(p => `
                <tr>
                    <td>${AtendeLabApi.escape(p.nome)}</td>
                    <td>${AtendeLabApi.escape(p.documento)}</td>
                    <td>${AtendeLabApi.escape(p.email)}</td>
                    <td>${AtendeLabApi.escape(p.curso || '')}</td>
                    <td>${AtendeLabApi.escape(p.periodo || '')}</td>
                    <td>
                        <span class="badge ${p.status === 'ativo' ? 'text-bg-success' : 'text-bg-secondary'}">
                            ${AtendeLabApi.escape(p.status)}
                        </span>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary" onclick="editarPessoa(${Number(p.id)})">
                            Editar
                        </button>

                        <button class="btn btn-sm btn-outline-danger" onclick="inativarPessoa(${Number(p.id)})">
                            Inativar
                        </button>
                    </td>
                </tr>
            `).join('');

        } catch (error) {
            AtendeLabApi.showAlert('alerta', error.message, 'danger');
        }
    }

    function abrirFormulario() {
        document.getElementById('cardFormulario').classList.remove('d-none');
        document.getElementById('tituloFormulario').textContent = 'Cadastrar pessoa';
        formPessoa.reset();
        document.getElementById('pessoaId').value = '';
    }

    function fecharFormulario() {
        document.getElementById('cardFormulario').classList.add('d-none');
        formPessoa.reset();
        document.getElementById('pessoaId').value = '';
    }

    async function editarPessoa(id) {
        try {
            const pessoa = AtendeLabApi.toObject(
                await AtendeLabApi.get('pessoas', 'buscarPorId', { id })
            );

            document.getElementById('pessoaId').value = pessoa.id;
            document.getElementById('nome').value = pessoa.nome || '';
            document.getElementById('documento').value = pessoa.documento || '';
            document.getElementById('telefone').value = pessoa.telefone || '';
            document.getElementById('email').value = pessoa.email || '';
            document.getElementById('curso').value = pessoa.curso || '';
            document.getElementById('periodo').value = pessoa.periodo || '';
            document.getElementById('status').value = pessoa.status || 'ativo';
            document.getElementById('observacoes').value = pessoa.observacoes || '';

            document.getElementById('tituloFormulario').textContent = 'Editar pessoa';
            document.getElementById('cardFormulario').classList.remove('d-none');

        } catch (error) {
            AtendeLabApi.showAlert('alerta', error.message, 'danger');
        }
    }

    async function inativarPessoa(id) {
        if (!confirm('Deseja inativar esta pessoa?')) {
            return;
        }

        try {
            const pessoa = AtendeLabApi.toObject(
                await AtendeLabApi.get('pessoas', 'buscarPorId', { id })
            );

            const form = new FormData();
            form.append('id', pessoa.id);
            form.append('nome', pessoa.nome || '');
            form.append('documento', pessoa.documento || '');
            form.append('telefone', pessoa.telefone || '');
            form.append('email', pessoa.email || '');
            form.append('curso', pessoa.curso || '');
            form.append('periodo', pessoa.periodo || '');
            form.append('observacoes', pessoa.observacoes || '');
            form.append('status', 'inativo');

            await AtendeLabApi.post('pessoas', 'atualizar', form);

            AtendeLabApi.showAlert('alerta', 'Pessoa inativada com sucesso.');
            await carregarPessoas();

        } catch (error) {
            AtendeLabApi.showAlert('alerta', error.message, 'danger');
        }
    }

    formPessoa.addEventListener('submit', async event => {
        event.preventDefault();

        const id = document.getElementById('pessoaId').value;

        try {
            await AtendeLabApi.post(
                'pessoas',
                id ? 'atualizar' : 'criar',
                new FormData(formPessoa)
            );

            AtendeLabApi.showAlert(
                'alerta',
                id ? 'Pessoa atualizada com sucesso.' : 'Pessoa cadastrada com sucesso.'
            );

            fecharFormulario();
            await carregarPessoas();

        } catch (error) {
            AtendeLabApi.showAlert('alerta', error.message, 'danger');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        carregarPessoas();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>