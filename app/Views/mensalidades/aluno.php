<?php
/**
 * Situação financeira do aluno: plano ativo, adimplência, nova mensalidade e histórico (RF13, RF14, RF24).
 *
 * @var array<string, mixed>       $aluno
 * @var array<string, mixed>|null  $planoAtivo
 * @var bool                       $adimplente
 * @var list<array<string, mixed>> $mensalidades
 * @var list<array<string, mixed>> $planos        planos ativos para vincular
 */
$situacoes = \App\Models\MensalidadeModel::SITUACOES;
$estilos   = ['EM_ABERTO' => 'text-bg-info', 'PAGO' => 'text-bg-success', 'ATRASADO' => 'text-bg-danger'];
$periodicidades = \App\Models\PlanoModel::PERIODICIDADES;
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0"><?= esc($aluno['nome']) ?>
        <span class="badge <?= $adimplente ? 'text-bg-success' : 'text-bg-danger' ?> fs-6"><?= $adimplente ? 'Adimplente' : 'Inadimplente' ?></span>
    </h1>
    <a href="<?= url_to('alunos_ver', $aluno['id']) ?>" class="btn btn-outline-secondary">Ficha do aluno</a>
</div>

<section class="card card-body mb-4" aria-labelledby="tituloPlano">
    <h2 class="h5" id="tituloPlano">Plano atual</h2>
    <?php if ($planoAtivo === null) : ?>
        <p class="text-muted">Nenhum plano ativo. Vincule um plano para gerar mensalidades.</p>
    <?php else : ?>
        <p><strong><?= esc($planoAtivo['plano_nome']) ?></strong> ·
            <?= esc(formatar_moeda($planoAtivo['plano_valor'])) ?> (<?= esc($periodicidades[$planoAtivo['plano_periodicidade']] ?? '') ?>) ·
            desde <?= esc(formatar_data($planoAtivo['data_inicio'])) ?></p>
    <?php endif; ?>

    <form action="<?= url_to('mensalidades_vincular_plano', $aluno['id']) ?>" method="post" class="row g-2 align-items-end">
        <?= csrf_field() ?>
        <div class="col-12 col-md-6">
            <label for="plano_id" class="form-label"><?= $planoAtivo === null ? 'Vincular plano' : 'Trocar de plano' ?></label>
            <select class="form-select" id="plano_id" name="plano_id" required>
                <option value="">Selecione</option>
                <?php foreach ($planos as $plano) : ?>
                    <option value="<?= esc($plano['id'], 'attr') ?>"><?= esc($plano['nome']) ?> · <?= esc(formatar_moeda($plano['valor'])) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3"><button type="submit" class="btn btn-primary">Confirmar</button></div>
    </form>
</section>

<?php if ($planoAtivo !== null) : ?>
    <section class="card card-body mb-4" aria-labelledby="tituloNova">
        <h2 class="h5" id="tituloNova">Gerar mensalidade</h2>
        <form action="<?= url_to('mensalidades_criar', $aluno['id']) ?>" method="post" class="row g-2 align-items-end" novalidate>
            <?= csrf_field() ?>
            <div class="col-6 col-md-3">
                <label for="vencimento" class="form-label">Vencimento</label>
                <input type="date" class="form-control" id="vencimento" name="vencimento" required value="<?= esc(old('vencimento'), 'attr') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="valor" class="form-label">Valor <span class="text-muted">(vazio = valor do plano)</span></label>
                <input type="text" class="form-control" id="valor" name="valor" inputmode="decimal" value="<?= esc(old('valor'), 'attr') ?>">
            </div>
            <div class="col-12 col-md-3"><button type="submit" class="btn btn-outline-primary">Gerar</button></div>
        </form>
    </section>
<?php endif; ?>

<section aria-labelledby="tituloHistorico">
    <h2 class="h5" id="tituloHistorico">Histórico de mensalidades e quitações</h2>
    <div class="table-responsive">
        <table class="table table-striped align-middle bg-white">
            <caption class="visually-hidden">Mensalidades do aluno</caption>
            <thead>
                <tr>
                    <th scope="col">Vencimento</th>
                    <th scope="col">Plano</th>
                    <th scope="col">Valor</th>
                    <th scope="col">Situação</th>
                    <th scope="col">Pagamento</th>
                    <th scope="col"><span class="visually-hidden">Ações</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($mensalidades === []) : ?>
                    <tr><td colspan="6" class="text-center text-muted">Nenhuma mensalidade registrada.</td></tr>
                <?php endif; ?>
                <?php foreach ($mensalidades as $mensalidade) : ?>
                    <tr>
                        <td><?= esc(formatar_data($mensalidade['vencimento'])) ?></td>
                        <td><?= esc($mensalidade['plano_nome']) ?></td>
                        <td><?= esc(formatar_moeda($mensalidade['valor'])) ?></td>
                        <td><span class="badge <?= esc($estilos[$mensalidade['situacao']] ?? 'text-bg-secondary', 'attr') ?>"><?= esc($situacoes[$mensalidade['situacao']] ?? $mensalidade['situacao']) ?></span></td>
                        <td><?= $mensalidade['situacao'] === 'PAGO'
                            ? esc(formatar_data($mensalidade['data_pagamento']) . ' · ' . (\App\Models\MensalidadeModel::FORMAS_PAGAMENTO[$mensalidade['forma_pagamento']] ?? ''))
                            : '—' ?></td>
                        <td class="text-end"><?= $mensalidade['situacao'] === 'PAGO' ? '' : view('mensalidades/_quitacao', ['mensalidade' => $mensalidade]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?= $this->endSection() ?>
