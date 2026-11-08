<?php
/**
 * Lista de mensalidades com filtro por situação e quitação direto na linha (RF14, RF25).
 *
 * @var list<array<string, mixed>> $mensalidades
 * @var string                     $situacao
 * @var string                     $busca
 */
$situacoes = \App\Models\MensalidadeModel::SITUACOES;
$estilos   = ['EM_ABERTO' => 'text-bg-info', 'PAGO' => 'text-bg-success', 'ATRASADO' => 'text-bg-danger'];
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Mensalidades</h1>
    <a href="<?= url_to('mensalidades_inadimplentes') ?>" class="btn btn-outline-danger">Ver inadimplentes</a>
</div>

<form action="<?= url_to('mensalidades') ?>" method="get" class="row g-2 mb-3" role="search">
    <div class="col-12 col-md-5">
        <label for="busca" class="visually-hidden">Buscar aluno</label>
        <input type="search" class="form-control" id="busca" name="busca" value="<?= esc($busca) ?>" placeholder="Buscar por nome ou código do aluno">
    </div>
    <div class="col-12 col-md-3">
        <label for="situacao" class="visually-hidden">Situação</label>
        <select class="form-select" id="situacao" name="situacao">
            <option value="">Todas as situações</option>
            <?php foreach ($situacoes as $valor => $rotulo) : ?>
                <option value="<?= esc($valor, 'attr') ?>" <?= $situacao === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12 col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        <?php if ($busca !== '' || $situacao !== '') : ?>
            <a href="<?= url_to('mensalidades') ?>" class="btn btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped align-middle bg-white">
        <caption class="visually-hidden">Mensalidades</caption>
        <thead>
            <tr>
                <th scope="col">Aluno</th>
                <th scope="col">Plano</th>
                <th scope="col">Vencimento</th>
                <th scope="col">Valor</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($mensalidades === []) : ?>
                <tr><td colspan="6" class="text-center text-muted">Nenhuma mensalidade encontrada.</td></tr>
            <?php endif; ?>
            <?php foreach ($mensalidades as $mensalidade) : ?>
                <tr>
                    <td><a href="<?= url_to('mensalidades_aluno', $mensalidade['aluno_id']) ?>"><?= esc($mensalidade['aluno_codigo']) ?> · <?= esc($mensalidade['aluno_nome']) ?></a></td>
                    <td><?= esc($mensalidade['plano_nome']) ?></td>
                    <td><?= esc(formatar_data($mensalidade['vencimento'])) ?></td>
                    <td><?= esc(formatar_moeda($mensalidade['valor'])) ?></td>
                    <td>
                        <span class="badge <?= esc($estilos[$mensalidade['situacao']] ?? 'text-bg-secondary', 'attr') ?>"><?= esc($situacoes[$mensalidade['situacao']] ?? $mensalidade['situacao']) ?></span>
                        <?php if ($mensalidade['situacao'] === 'PAGO') : ?>
                            <small class="text-muted d-block"><?= esc(formatar_data($mensalidade['data_pagamento'])) ?> · <?= esc(\App\Models\MensalidadeModel::FORMAS_PAGAMENTO[$mensalidade['forma_pagamento']] ?? '') ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?= $mensalidade['situacao'] === 'PAGO' ? '' : view('mensalidades/_quitacao', ['mensalidade' => $mensalidade]) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<nav aria-label="Paginação das mensalidades"><?= $pager->links() ?></nav>
<?= $this->endSection() ?>
