<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Alunos</h1>
    <a href="<?= url_to('alunos_novo') ?>" class="btn btn-primary">Novo aluno</a>
</div>

<form action="<?= url_to('alunos') ?>" method="get" class="row g-2 mb-3" role="search">
    <div class="col-12 col-md-8">
        <label for="busca" class="visually-hidden">Buscar aluno</label>
        <input type="search" class="form-control" id="busca" name="busca" value="<?= esc($busca) ?>"
               placeholder="Buscar por nome, código ou CPF">
    </div>
    <div class="col-12 col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
        <?php if ($busca !== '') : ?>
            <a href="<?= url_to('alunos') ?>" class="btn btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
        <caption class="visually-hidden">Lista de alunos cadastrados</caption>
        <thead>
            <tr>
                <th scope="col">Código</th>
                <th scope="col">Nome</th>
                <th scope="col">CPF</th>
                <th scope="col">Telefone</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($alunos === []) : ?>
                <tr><td colspan="6" class="text-center text-muted">Nenhum aluno encontrado.</td></tr>
            <?php endif; ?>
            <?php foreach ($alunos as $aluno) : ?>
                <tr>
                    <td><?= esc($aluno['codigo']) ?></td>
                    <td><?= esc($aluno['nome']) ?></td>
                    <td><?= esc(formatar_cpf($aluno['cpf'])) ?></td>
                    <td><?= esc(formatar_telefone($aluno['telefone'])) ?></td>
                    <td>
                        <span class="badge <?= $aluno['situacao'] === 'ATIVO' ? 'text-bg-success' : 'text-bg-secondary' ?>">
                            <?= esc($aluno['situacao'] === 'ATIVO' ? 'Ativo' : 'Inativo') ?>
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url_to('alunos_ver', $aluno['id']) ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                        <a href="<?= url_to('alunos_editar', $aluno['id']) ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<nav aria-label="Paginação da lista de alunos"><?= $pager->links() ?></nav>
<?= $this->endSection() ?>
