<?php
/**
 * Mini formulário de quitação de uma mensalidade (RF14, RF25), reaproveitado pelas listas.
 *
 * @var array<string, mixed> $mensalidade
 */
$formas = \App\Models\MensalidadeModel::FORMAS_PAGAMENTO;
?>
<form action="<?= url_to('mensalidades_quitar', $mensalidade['id']) ?>" method="post" class="d-flex flex-wrap gap-1 justify-content-end">
    <?= csrf_field() ?>
    <label class="visually-hidden" for="data_<?= esc($mensalidade['id'], 'attr') ?>">Data do pagamento</label>
    <input type="date" class="form-control form-control-sm w-auto" id="data_<?= esc($mensalidade['id'], 'attr') ?>"
           name="data_pagamento" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
    <label class="visually-hidden" for="forma_<?= esc($mensalidade['id'], 'attr') ?>">Forma de pagamento</label>
    <select class="form-select form-select-sm w-auto" id="forma_<?= esc($mensalidade['id'], 'attr') ?>" name="forma_pagamento" required>
        <option value="">Forma</option>
        <?php foreach ($formas as $valor => $rotulo) : ?>
            <option value="<?= esc($valor, 'attr') ?>"><?= esc($rotulo) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-sm btn-success">Quitar</button>
</form>
