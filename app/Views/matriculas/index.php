<?php
/**
 * Lista de matrículas ativas (HU03).
 *
 * @var list<array<string, mixed>> $matriculas
 * @var string                     $busca
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Matrículas</h1>
    <a href="<?= esc(url_to('matriculas_nova'), 'html') ?>" class="btn btn-primary">Nova matrícula</a>
</header>

<form action="<?= esc(url_to('matriculas'), 'html') ?>" method="get" class="row g-2 mb-3" role="search">
    <div class="col-12 col-md-8">
        <label for="busca" class="visually-hidden">Buscar matrícula</label>
        <input type="search" class="form-control" id="busca" name="busca" value="<?= esc($busca) ?>" placeholder="Buscar por nome ou código do aluno">
    </div>
    <div class="col-12 col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
        <?php if ($busca !== '') : ?>
            <a href="<?= esc(url_to('matriculas'), 'html') ?>" class="btn btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="ct-card mb-3">
<div class="table-responsive position-relative">
    <table class="table table-striped table-hover align-middle">
        <caption class="visually-hidden">Matrículas ativas</caption>
        <thead>
            <tr>
                <th scope="col">Aluno</th>
                <th scope="col">Turma</th>
                <th scope="col">Modalidade</th>
                <th scope="col">Desde</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($matriculas === []) : ?>
                <tr><td colspan="5" data-label=""><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhuma matrícula ativa encontrada.</p></div></td></tr>
            <?php endif; ?>
            <?php foreach ($matriculas as $matricula) : ?>
                <tr>
                    <td><?= esc($matricula['aluno_codigo']) ?> · <?= esc($matricula['aluno_nome']) ?></td>
                    <td><?= esc($matricula['turma_nome']) ?><?= $matricula['turma_situacao'] === 'SUSPENSA' ? ' (suspensa)' : '' ?></td>
                    <td><?= esc($matricula['modalidade_nome']) ?></td>
                    <td><?= esc(formatar_data($matricula['data_matricula'])) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= esc(url_to('matriculas_ver', $matricula['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                        <a href="<?= esc(url_to('matriculas_trocar', $matricula['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Trocar de turma</a>
                        <form action="<?= esc(url_to('matriculas_cancelar', $matricula['id']), 'html') ?>" method="post" class="d-inline"
                              onsubmit="return confirm('Cancelar esta matrícula?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Cancelar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>

<nav aria-label="Paginação das matrículas"><?= $pager->links() ?></nav>
<?= $this->endSection() ?>
