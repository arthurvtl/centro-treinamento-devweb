<?php
/**
 * Histórico de vendas da loja (RF27, RF28).
 *
 * @var list<array<string, mixed>> $vendas
 * @var \CodeIgniter\Pager\Pager   $pager
 * @var array{quantidade: int, total: float} $hoje
 * @var list<array<string, mixed>> $baixos produtos com estoque baixo
 */
$formas = rotulos_de('formas_pagamento');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Loja</h1>
    <div class="d-flex gap-2">
        <a href="<?= esc(url_to('produtos'), 'html') ?>" class="btn btn-outline-secondary">Produtos e estoque</a>
        <a href="<?= esc(url_to('vendas_nova'), 'html') ?>" class="btn btn-primary">Nova venda</a>
    </div>
</header>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiVendasHoje', 'rotulo' => 'Vendas hoje', 'valor' => (string) $hoje['quantidade'], 'legenda' => formatar_moeda($hoje['total']) . ' vendidos']) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiEstoqueBaixo', 'rotulo' => 'Estoque baixo', 'valor' => (string) count($baixos), 'legenda' => 'produtos para repor', 'alerta' => $baixos !== []]) ?></div>
</div>

<?php if ($baixos !== []) : ?>
    <div class="alert alert-secondary" role="status">
        <strong>Repor estoque:</strong>
        <?= esc(implode(', ', array_map(static fn (array $p): string => $p['nome'] . ' (' . $p['estoque_atual'] . ')', $baixos))) ?>
    </div>
<?php endif; ?>

<div class="ct-card mb-3">
<div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Lista de vendas">
    <table class="table table-striped table-hover align-middle">
        <caption class="visually-hidden">Vendas da loja</caption>
        <thead>
            <tr>
                <th scope="col">Nº</th>
                <th scope="col">Data</th>
                <th scope="col">Cliente</th>
                <th scope="col">Pagamento</th>
                <th scope="col" class="num">Total</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($vendas === []) : ?>
                <tr><td colspan="7"><div class="ct-vazio">
                    <p class="mb-1 fw-semibold">Nenhuma venda registrada.</p>
                    <a href="<?= esc(url_to('vendas_nova'), 'html') ?>" class="btn btn-outline-secondary btn-sm">Registrar venda</a>
                </div></td></tr>
            <?php endif; ?>
            <?php foreach ($vendas as $venda) : ?>
                <tr>
                    <td><?= esc((string) $venda['id']) ?></td>
                    <td><?= esc(date('d/m/Y H:i', strtotime($venda['data_venda']))) ?></td>
                    <td><?= $venda['aluno_nome'] !== null ? esc($venda['aluno_codigo'] . ' · ' . $venda['aluno_nome']) : 'Cliente avulso' ?></td>
                    <td><?= esc($formas[$venda['forma_pagamento']] ?? $venda['forma_pagamento']) ?></td>
                    <td class="num"><?= esc(formatar_moeda($venda['valor_total'])) ?></td>
                    <td><span class="ct-status <?= $venda['situacao'] === 'CONCLUIDA' ? 'ct-status--ok' : 'ct-status--inativo' ?>"><?= $venda['situacao'] === 'CONCLUIDA' ? 'Concluída' : 'Cancelada' ?></span></td>
                    <td class="text-end"><a href="<?= esc(url_to('vendas_ver', $venda['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
<nav aria-label="Paginação da lista de vendas"><?= $pager->links() ?></nav>
<?= $this->endSection() ?>
