<?php
/**
 * Detalhes de uma venda e cancelamento (RF28).
 *
 * @var array<string, mixed>       $venda
 * @var list<array<string, mixed>> $itens
 */
$formas = rotulos_de('formas_pagamento');
$concluida = $venda['situacao'] === 'CONCLUIDA';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Venda #<?= esc((string) $venda['id']) ?>
        <span class="ct-status <?= $concluida ? 'ct-status--ok' : 'ct-status--inativo' ?> ms-2"><?= $concluida ? 'Concluída' : 'Cancelada' ?></span>
    </h1>
    <a href="<?= esc(url_to('vendas'), 'html') ?>" class="btn btn-outline-secondary">Voltar</a>
</header>

<dl class="row mb-4">
    <dt class="col-sm-3">Data</dt><dd class="col-sm-9"><?= esc(date('d/m/Y H:i', strtotime($venda['data_venda']))) ?></dd>
    <dt class="col-sm-3">Cliente</dt><dd class="col-sm-9"><?= $venda['aluno_nome'] !== null ? esc($venda['aluno_codigo'] . ' · ' . $venda['aluno_nome']) : 'Cliente avulso' ?></dd>
    <dt class="col-sm-3">Pagamento</dt><dd class="col-sm-9"><?= esc($formas[$venda['forma_pagamento']] ?? $venda['forma_pagamento']) ?></dd>
    <dt class="col-sm-3">Vendedor</dt><dd class="col-sm-9"><?= esc($venda['vendedor_nome']) ?></dd>
</dl>

<div class="ct-card mb-3">
<div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Produtos da venda">
    <table class="table table-striped align-middle">
        <caption class="visually-hidden">Produtos da venda</caption>
        <thead>
            <tr>
                <th scope="col">Produto</th>
                <th scope="col" class="num">Quantidade</th>
                <th scope="col" class="num">Preço unitário</th>
                <th scope="col" class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item) : ?>
                <tr>
                    <td><?= esc($item['produto_nome']) ?></td>
                    <td class="num"><?= esc((string) $item['quantidade']) ?></td>
                    <td class="num"><?= esc(formatar_moeda($item['preco_unitario'])) ?></td>
                    <td class="num"><?= esc(formatar_moeda($item['subtotal'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><th scope="row" colspan="3" class="text-end">Total</th><td class="num fw-bold"><?= esc(formatar_moeda($venda['valor_total'])) ?></td></tr>
        </tfoot>
    </table>
</div>
</div>

<?php if ($concluida) : ?>
    <form action="<?= esc(url_to('vendas_cancelar', $venda['id']), 'html') ?>" method="post"
          onsubmit="return confirm('Cancelar esta venda? Os produtos voltam ao estoque.');">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger">Cancelar venda</button>
    </form>
<?php endif; ?>
<?= $this->endSection() ?>
