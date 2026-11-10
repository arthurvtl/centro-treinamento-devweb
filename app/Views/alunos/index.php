<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Alunos</h1>
    <a href="<?= esc(url_to('alunos_novo'), 'html') ?>" class="btn btn-primary">Novo aluno</a>
</header>

<form action="<?= esc(url_to('alunos'), 'html') ?>" method="get" class="row g-2 mb-3" role="search">
    <div class="col-12 col-md-8">
        <label for="busca" class="visually-hidden">Buscar aluno</label>
        <input type="search" class="form-control" id="busca" name="busca" value="<?= esc($busca) ?>"
               placeholder="Buscar por nome, código ou CPF">
    </div>
    <div class="col-12 col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
        <?php if ($busca !== '') : ?>
            <a href="<?= esc(url_to('alunos'), 'html') ?>" class="btn btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="ct-card mb-3">
<div class="table-responsive position-relative">
    <table class="table table-striped table-hover align-middle">
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
                <tr><td colspan="6"><div class="ct-vazio">
                    <p class="mb-1 fw-semibold">Nenhum aluno encontrado.</p>
                    <?php if ($busca !== '') : ?>
                        <p class="text-secondary small mb-3">Confira a grafia ou busque pelo código.</p>
                        <a href="<?= esc(url_to('alunos'), 'html') ?>" class="btn btn-outline-secondary btn-sm">Limpar busca</a>
                    <?php else : ?>
                        <p class="text-secondary small mb-3">Comece cadastrando o primeiro aluno.</p>
                    <a href="<?= esc(url_to('alunos_novo'), 'html') ?>" class="btn btn-outline-secondary btn-sm"><?= esc('Cadastrar aluno') ?></a>
                    <?php endif; ?>
                </div></td></tr>
            <?php endif; ?>
            <?php foreach ($alunos as $aluno) : ?>
                <tr>
                    <td><?= esc($aluno['codigo']) ?></td>
                    <td><?= esc($aluno['nome']) ?></td>
                    <td><?= esc(formatar_cpf($aluno['cpf'])) ?></td>
                    <td><?= esc(formatar_telefone($aluno['telefone'])) ?></td>
                    <td>
                        <span class="ct-status <?= $aluno['situacao'] === 'ATIVO' ? 'ct-status--ok' : 'ct-status--inativo' ?>">
                            <?= esc($aluno['situacao'] === 'ATIVO' ? 'Ativo' : 'Inativo') ?>
                        </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="<?= esc(url_to('alunos_ver', $aluno['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                        <a href="<?= esc(url_to('alunos_editar', $aluno['id']), 'html') ?>" class="btn btn-sm btn-link">Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>

<nav aria-label="Paginação da lista de alunos"><?= $pager->links() ?></nav>
<?= $this->endSection() ?>
