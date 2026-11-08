<?php
/**
 * Formulário de cadastro e edição de professor, com as modalidades em que ele pode atuar (RF04, RN03).
 *
 * @var array<string, mixed>       $professor            valores atuais (vazio no cadastro)
 * @var list<array<string, mixed>> $modalidades          todas as modalidades disponíveis
 * @var list<int>                  $modalidadesMarcadas  ids habilitados hoje
 * @var string                     $acao                 URL de destino do POST
 */
// Após erro de validação, prevalece o que o usuário tinha marcado.
$marcadas = array_map('intval', (array) old('modalidades', $modalidadesMarcadas));
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<h1 class="h3 mb-3"><?= esc($titulo) ?></h1>

<form action="<?= esc($acao, 'attr') ?>" method="post" novalidate>
    <?= csrf_field() ?>

    <fieldset class="card card-body mb-4">
        <legend class="h5">Dados do professor</legend>
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label for="nome" class="form-label">Nome completo</label>
                <input type="text" class="form-control" id="nome" name="nome" maxlength="120" required
                       value="<?= esc(old('nome', $professor['nome'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" class="form-control" id="cpf" name="cpf" inputmode="numeric" maxlength="14" required
                       value="<?= esc(old('cpf', isset($professor['cpf']) ? formatar_cpf($professor['cpf']) : '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="telefone" class="form-label">Telefone</label>
                <input type="tel" class="form-control" id="telefone" name="telefone" maxlength="20" required
                       value="<?= esc(old('telefone', isset($professor['telefone']) ? formatar_telefone($professor['telefone']) : '')) ?>">
            </div>
            <div class="col-12 col-md-8">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" maxlength="150" required
                       value="<?= esc(old('email', $professor['email'] ?? '')) ?>">
            </div>
        </div>
    </fieldset>

    <fieldset class="card card-body mb-4">
        <legend class="h5">Modalidades que pode ministrar</legend>
        <?php if ($modalidades === []) : ?>
            <p class="text-muted mb-0">Cadastre uma modalidade antes de cadastrar professores.</p>
        <?php endif; ?>
        <div class="row">
            <?php foreach ($modalidades as $modalidade) : ?>
                <div class="col-12 col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="modalidades[]" id="modalidade_<?= esc($modalidade['id'], 'attr') ?>"
                               value="<?= esc($modalidade['id'], 'attr') ?>" <?= in_array((int) $modalidade['id'], $marcadas, true) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="modalidade_<?= esc($modalidade['id'], 'attr') ?>"><?= esc($modalidade['nome']) ?></label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= url_to('professores') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
