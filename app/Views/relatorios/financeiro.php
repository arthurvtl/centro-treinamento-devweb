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
<header class="ct-page-header">
    <h1 class="h3 mb-0">Relatório financeiro</h1>
    <a href="<?= esc(url_to('relatorios'), 'html') ?>" class="btn btn-outline-secondary">Outros relatórios</a>
</header>

<form action="<?= esc(url_to('relatorios_financeiro'), 'html') ?>" method="get" class="row g-2 mb-4">
    <div class="col-12 col-md-4">
        <label for="mes" class="form-label">Mês de referência</label>
        <input type="month" class="form-control" id="mes" name="mes" value="<?= esc($mes, 'attr') ?>" required>
    </div>
    <div class="col-12 col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-outline-primary">Consultar</button></div>
</form>

<p class="text-muted">Período consultado: <strong><?= esc($numeroMes . '/' . $ano) ?></strong> · <?= esc((string) $resumo['quantidade']) ?> mensalidade(s) com vencimento no mês.</p>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiPrevisto', 'rotulo' => 'Previsto', 'valor' => formatar_moeda($resumo['previsto']), 'legenda' => 'vencimentos do mês']) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiRecebido', 'rotulo' => 'Recebido no mês', 'valor' => formatar_moeda($resumo['recebido']), 'legenda' => 'pagamentos feitos no mês']) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiEmAberto', 'rotulo' => 'Em aberto', 'valor' => formatar_moeda($resumo['em_aberto']), 'legenda' => 'a vencer']) ?></div>
    <div class="col-6 col-lg-3"><?= view('componentes/indicador', ['id' => 'kpiAtrasado', 'rotulo' => 'Atrasado', 'valor' => formatar_moeda($resumo['atrasado']), 'legenda' => 'vencido e não pago', 'alerta' => true]) ?></div>
</div>

<section aria-labelledby="tituloFormas">
    <h2 class="h5" id="tituloFormas">Recebido por forma de pagamento</h2>
    <div class="ct-card mb-3">
<div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Recebido por forma de pagamento">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">Recebimentos por forma de pagamento</caption>
            <thead><tr><th scope="col">Forma</th><th scope="col" class="num">Total recebido</th></tr></thead>
            <tbody>
                <?php if ($resumo['por_forma'] === []) : ?>
                    <tr><td colspan="2"><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhum recebimento neste mês.</p></div></td></tr>
                <?php endif; ?>
                <?php foreach ($resumo['por_forma'] as $forma => $total) : ?>
                    <tr><td><?= esc($formas[$forma] ?? $forma) ?></td><td class="num"><?= esc(formatar_moeda($total)) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</section>
<?= $this->endSection() ?>
