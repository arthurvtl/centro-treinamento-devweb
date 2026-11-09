<?php
/**
 * Lista de mensalidades com filtro por situação e quitação direto na linha (RF14, RF25).
 *
 * @var list<array<string, mixed>> $mensalidades
 * @var string                     $situacao
 * @var string                     $busca
 */
$situacoes = rotulos_de('situacoes_mensalidade');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Mensalidades</h1>
    <a href="<?= esc(url_to('mensalidades_inadimplentes'), 'html') ?>" class="btn btn-outline-secondary">Ver inadimplentes</a>
</header>

<form action="<?= esc(url_to('mensalidades'), 'html') ?>" method="get" class="row g-2 mb-3" role="search">
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
            <a href="<?= esc(url_to('mensalidades'), 'html') ?>" class="btn btn-outline-secondary">Limpar</a>
        <?php endif; ?>
    </div>
</form>

<div class="ct-card mb-3">
<div class="table-responsive position-relative">
    <table class="table table-striped table-hover align-middle">
        <caption class="visually-hidden">Mensalidades</caption>
        <thead>
            <tr>
                <th scope="col">Aluno</th>
                <th scope="col">Plano</th>
                <th scope="col">Vencimento</th>
                <th scope="col" class="num">Valor</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($mensalidades === []) : ?>
                <tr><td colspan="6" data-label=""><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhuma mensalidade encontrada.</p></div></td></tr>
            <?php endif; ?>
            <?php foreach ($mensalidades as $mensalidade) : ?>
                <tr>
                    <td><a href="<?= esc(url_to('mensalidades_aluno', $mensalidade['aluno_id']), 'html') ?>"><?= esc($mensalidade['aluno_codigo']) ?> · <?= esc($mensalidade['aluno_nome']) ?></a></td>
                    <td><?= esc($mensalidade['plano_nome']) ?></td>
                    <td><?= esc(formatar_data($mensalidade['vencimento'])) ?></td>
                    <td class="num"><?= esc(formatar_moeda($mensalidade['valor'])) ?></td>
                    <td>
                        <span class="ct-status <?= esc(classe_status($mensalidade['situacao']), 'attr') ?>"><?= esc($situacoes[$mensalidade['situacao']] ?? $mensalidade['situacao']) ?></span>
                        <?php if ($mensalidade['situacao'] === 'PAGO') : ?>
                            <small class="text-muted d-block"><?= esc(formatar_data($mensalidade['data_pagamento'])) ?> · <?= esc(rotulos_de('formas_pagamento')[$mensalidade['forma_pagamento']] ?? '') ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if ($mensalidade['situacao'] !== 'PAGO') : ?>
                            <?= view('mensalidades/_quitacao', ['mensalidade' => $mensalidade]) ?>
                            <?= view('mensalidades/_acoes', ['mensalidade' => $mensalidade]) ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>

<nav aria-label="Paginação das mensalidades"><?= $pager->links() ?></nav>
<?= $this->endSection() ?>
