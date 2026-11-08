<?php
/**
 * Relatório financeiro mensal, restrito ao proprietário (RF19, RN05).
 *
 * @var string $mes    período no formato AAAA-MM
 * @var array{previsto: float, recebido: float, em_aberto: float, atrasado: float, quantidade: int, por_forma: array<string, float>} $resumo
 */
$formas = rotulos_de('formas_pagamento');
[$ano, $numeroMes] = explode('-', $mes);
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Relatório financeiro</h1>
    <a href="<?= esc(url_to('relatorios'), 'html') ?>" class="btn btn-outline-secondary">Outros relatórios</a>
</div>

<form action="<?= esc(url_to('relatorios_financeiro'), 'html') ?>" method="get" class="row g-2 mb-4">
    <div class="col-12 col-md-4">
        <label for="mes" class="form-label">Mês de referência</label>
        <input type="month" class="form-control" id="mes" name="mes" value="<?= esc($mes, 'attr') ?>" required>
    </div>
    <div class="col-12 col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-outline-primary">Consultar</button></div>
</form>

<p class="text-muted">Período consultado: <strong><?= esc($numeroMes . '/' . $ano) ?></strong> · <?= esc((string) $resumo['quantidade']) ?> mensalidade(s) com vencimento no mês.</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card card-body"><span class="text-muted">Previsto (vencimentos)</span><span class="fs-4 fw-bold"><?= esc(formatar_moeda($resumo['previsto'])) ?></span></div></div>
    <div class="col-6 col-lg-3"><div class="card card-body"><span class="text-muted">Recebido no mês</span><span class="fs-4 fw-bold text-success"><?= esc(formatar_moeda($resumo['recebido'])) ?></span></div></div>
    <div class="col-6 col-lg-3"><div class="card card-body"><span class="text-muted">Em aberto</span><span class="fs-4 fw-bold text-info"><?= esc(formatar_moeda($resumo['em_aberto'])) ?></span></div></div>
    <div class="col-6 col-lg-3"><div class="card card-body"><span class="text-muted">Atrasado</span><span class="fs-4 fw-bold text-danger"><?= esc(formatar_moeda($resumo['atrasado'])) ?></span></div></div>
</div>

<section aria-labelledby="tituloFormas">
    <h2 class="h5" id="tituloFormas">Recebido por forma de pagamento</h2>
    <div class="table-responsive position-relative">
        <table class="table table-striped bg-white align-middle">
            <caption class="visually-hidden">Recebimentos por forma de pagamento</caption>
            <thead><tr><th scope="col">Forma</th><th scope="col">Total recebido</th></tr></thead>
            <tbody>
                <?php if ($resumo['por_forma'] === []) : ?>
                    <tr><td colspan="2" class="text-center text-muted">Nenhum recebimento neste mês.</td></tr>
                <?php endif; ?>
                <?php foreach ($resumo['por_forma'] as $forma => $total) : ?>
                    <tr><td><?= esc($formas[$forma] ?? $forma) ?></td><td><?= esc(formatar_moeda($total)) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?= $this->endSection() ?>
