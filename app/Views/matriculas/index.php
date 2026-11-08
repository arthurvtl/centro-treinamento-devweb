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
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Matrículas</h1>
    <a href="<?= url_to('matriculas_nova') ?>" class="btn btn-primary">Nova matrícula</a>
</div>

<form action="<?= url_to('matriculas') ?>" method="get" class="row g-2 mb-3" role="search">
    <div class="col-12 col-md-8">
        <label for="busca" class="visually-hidden">Buscar matrícula</label>
        <input type="search" class="form-control" id="busca" name="busca" value="<?= esc($busca) ?>" placeholder="Buscar por nome ou código do aluno">
    </div>
    <div class="col-12 col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-outline-primary">Buscar</button>
        <?php if ($busca !== '') : ?>
            <a href="<?= url_to('matriculas') ?>" class="btn btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
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
                <tr><td colspan="5" class="text-center text-muted">Nenhuma matrícula ativa encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($matriculas as $matricula) : ?>
                <tr>
                    <td><?= esc($matricula['aluno_codigo']) ?> · <?= esc($matricula['aluno_nome']) ?></td>
                    <td><?= esc($matricula['turma_nome']) ?><?= $matricula['turma_situacao'] === 'SUSPENSA' ? ' (suspensa)' : '' ?></td>
                    <td><?= esc($matricula['modalidade_nome']) ?></td>
                    <td><?= esc(formatar_data($matricula['data_matricula'])) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url_to('matriculas_trocar', $matricula['id']) ?>" class="btn btn-sm btn-outline-secondary">Trocar de turma</a>
                        <form action="<?= url_to('matriculas_cancelar', $matricula['id']) ?>" method="post" class="d-inline"
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

<nav aria-label="Paginação das matrículas"><?= $pager->links() ?></nav>
<?= $this->endSection() ?>
