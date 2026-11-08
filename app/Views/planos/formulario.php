<?php
/**
 * Formulário de cadastro e edição de plano (RF12).
 *
 * @var array<string, mixed> $plano
 * @var string               $acao
 */
$periodicidades = rotulos_de('periodicidades');
$periodicidadeAtual = old('periodicidade', $plano['periodicidade'] ?? '');
$valorAtual = old('valor', isset($plano['valor']) ? number_format((float) $plano['valor'], 2, ',', '') : '');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<h1 class="h3 mb-3"><?= esc($titulo) ?></h1>

<form action="<?= esc($acao, 'html') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="card card-body mb-3">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="nome" class="form-label">Nome do plano</label>
                <input type="text" class="form-control" id="nome" name="nome" maxlength="80" required
                       value="<?= esc(old('nome', $plano['nome'] ?? '')) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="valor" class="form-label">Valor (R$)</label>
                <input type="text" class="form-control" id="valor" name="valor" inputmode="decimal" required
                       value="<?= esc($valorAtual) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="periodicidade" class="form-label">Periodicidade</label>
                <select class="form-select" id="periodicidade" name="periodicidade" required>
                    <option value="">Selecione</option>
                    <?php foreach ($periodicidades as $valor => $rotulo) : ?>
                        <option value="<?= esc($valor, 'attr') ?>" <?= $periodicidadeAtual === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12">
                <label for="beneficios" class="form-label">Benefícios <span class="text-muted">(opcional)</span></label>
                <textarea class="form-control" id="beneficios" name="beneficios" rows="2" maxlength="1000"><?= esc(old('beneficios', $plano['beneficios'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= esc(url_to('planos'), 'html') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
