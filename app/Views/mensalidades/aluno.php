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
$situacoes = rotulos_de('situacoes_mensalidade');
$periodicidades = rotulos_de('periodicidades');
// Recepcionista atende o aluno (vincula plano e dá baixa), mas só o proprietário gera cobranças (RN05).
$ehProprietario = session()->get('usuario_perfil') === 'proprietario';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0"><?= esc($aluno['nome']) ?>
        <span class="ct-status <?= $adimplente ? 'ct-status--ok' : 'ct-status--alerta' ?>"><?= $adimplente ? 'Adimplente' : 'Inadimplente' ?></span>
    </h1>
    <a href="<?= esc(url_to('alunos_ver', $aluno['id']), 'html') ?>" class="btn btn-outline-secondary">Ficha do aluno</a>
</header>

<section class="card card-body mb-4" aria-labelledby="tituloPlano">
    <h2 class="h5" id="tituloPlano">Plano atual</h2>
    <?php if ($planoAtivo === null) : ?>
        <p class="text-muted">Nenhum plano ativo. Vincule um plano para gerar mensalidades.</p>
    <?php else : ?>
        <p><strong><?= esc($planoAtivo['plano_nome']) ?></strong> ·
            <?= esc(formatar_moeda($planoAtivo['plano_valor'])) ?> (<?= esc($periodicidades[$planoAtivo['plano_periodicidade']] ?? '') ?>) ·
            desde <?= esc(formatar_data($planoAtivo['data_inicio'])) ?></p>
    <?php endif; ?>

    <form action="<?= esc(url_to('mensalidades_vincular_plano', $aluno['id']), 'html') ?>" method="post" class="row g-2 align-items-end">
        <?= csrf_field() ?>
        <div class="col-12 col-md-6">
            <label for="plano_id" class="form-label"><?= $planoAtivo === null ? 'Vincular plano' : 'Trocar de plano' ?></label>
            <select class="form-select" id="plano_id" name="plano_id" required>
                <option value="">Selecione</option>
                <?php foreach ($planos as $plano) : ?>
                    <option value="<?= esc($plano['id'], 'attr') ?>" <?= (int) old('plano_id') === (int) $plano['id'] ? 'selected' : '' ?>><?= esc($plano['nome']) ?> · <?= esc(formatar_moeda($plano['valor'])) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3"><button type="submit" class="btn btn-primary">Confirmar</button></div>
    </form>
</section>

<?php if ($planoAtivo !== null && $ehProprietario) : ?>
    <section class="card card-body mb-4" aria-labelledby="tituloNova">
        <h2 class="h5" id="tituloNova">Gerar mensalidade</h2>
        <form action="<?= esc(url_to('mensalidades_criar', $aluno['id']), 'html') ?>" method="post" class="row g-2 align-items-end" novalidate>
            <?= csrf_field() ?>
            <div class="col-6 col-md-3">
                <label for="vencimento" class="form-label">Vencimento</label>
                <input type="date" class="form-control" id="vencimento" name="vencimento" required value="<?= esc(old('vencimento'), 'attr') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="valor" class="form-label">Valor <span class="opcional">(vazio = valor do plano)</span></label>
                <input type="text" class="form-control" id="valor" name="valor" inputmode="decimal" value="<?= esc(old('valor'), 'attr') ?>">
            </div>
            <div class="col-12 col-md-3"><button type="submit" class="btn btn-outline-primary">Gerar</button></div>
        </form>
    </section>
<?php endif; ?>

<section aria-labelledby="tituloHistorico">
    <h2 class="h5" id="tituloHistorico">Histórico de mensalidades e quitações</h2>
    <div class="ct-card mb-3">
<div class="table-responsive position-relative">
        <table class="table table-striped table-hover align-middle">
            <caption class="visually-hidden">Mensalidades do aluno</caption>
            <thead>
                <tr>
                    <th scope="col">Vencimento</th>
                    <th scope="col">Plano</th>
                    <th scope="col" class="num">Valor</th>
                    <th scope="col">Situação</th>
                    <th scope="col">Pagamento</th>
                    <th scope="col"><span class="visually-hidden">Ações</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($mensalidades === []) : ?>
                    <tr><td colspan="6" data-label=""><div class="ct-vazio"><p class="mb-0 fw-semibold">Nenhuma mensalidade registrada.</p></div></td></tr>
                <?php endif; ?>
                <?php foreach ($mensalidades as $mensalidade) : ?>
                    <tr>
                        <td><?= esc(formatar_data($mensalidade['vencimento'])) ?></td>
                        <td><?= esc($mensalidade['plano_nome']) ?></td>
                        <td class="num"><?= esc(formatar_moeda($mensalidade['valor'])) ?></td>
                        <td><span class="ct-status <?= esc(classe_status($mensalidade['situacao']), 'attr') ?>"><?= esc($situacoes[$mensalidade['situacao']] ?? $mensalidade['situacao']) ?></span></td>
                        <td><?= $mensalidade['situacao'] === 'PAGO'
                            ? esc(formatar_data($mensalidade['data_pagamento']) . ' · ' . (rotulos_de('formas_pagamento')[$mensalidade['forma_pagamento']] ?? ''))
                            : '—' ?></td>
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
</section>
<?= $this->endSection() ?>
