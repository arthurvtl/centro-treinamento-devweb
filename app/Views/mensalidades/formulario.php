<?php
/**
 * Formulário para corrigir valor e vencimento de uma mensalidade ainda não paga (RF14).
 *
 * @var array<string, mixed> $mensalidade
 */
$valorAtual = old('valor', number_format((float) $mensalidade['valor'], 2, ',', ''));
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <div>
        <h1 class="h3 mb-0"><?= esc($titulo) ?></h1>
        <p class="text-secondary small mb-0">
    <?= esc($mensalidade['aluno_codigo']) ?> · <?= esc($mensalidade['aluno_nome']) ?> · plano <?= esc($mensalidade['plano_nome']) ?>
</p>
    </div>
</header>

<form action="<?= esc(url_to('mensalidades_atualizar', $mensalidade['id']), 'html') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="card card-body mb-3">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label for="vencimento" class="form-label">Vencimento</label>
                <input type="date" class="form-control" id="vencimento" name="vencimento" required
                       value="<?= esc(old('vencimento', $mensalidade['vencimento']), 'attr') ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="valor" class="form-label">Valor (R$)</label>
                <input type="text" class="form-control" id="valor" name="valor" inputmode="decimal" required
                       value="<?= esc($valorAtual, 'attr') ?>">
            </div>
        </div>
    </div>
    <div class="ct-form-acoes">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= esc(url_to('mensalidades_aluno', $mensalidade['aluno_id']), 'html') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
