<?php
/**
 * Mini formulário de quitação de uma mensalidade (RF14, RF25), reaproveitado pelas listas.
 *
 * @var array<string, mixed> $mensalidade
 */
$formas = rotulos_de('formas_pagamento');
// Se a quitação desta mensalidade falhou na validação, devolve o que foi digitado.
$refazer = (string) old('mensalidade_id') === (string) $mensalidade['id'];
$dataPagamento = $refazer ? (string) old('data_pagamento') : date('Y-m-d');
$formaEscolhida = $refazer ? (string) old('forma_pagamento') : '';
?>
<form action="<?= esc(url_to('mensalidades_quitar', $mensalidade['id']), 'html') ?>" method="post" class="d-flex flex-wrap gap-1 justify-content-end">
    <?= csrf_field() ?>
    <input type="hidden" name="mensalidade_id" value="<?= esc($mensalidade['id'], 'attr') ?>">
    <label class="visually-hidden" for="data_<?= esc($mensalidade['id'], 'attr') ?>">Data do pagamento</label>
    <input type="date" class="form-control form-control-sm w-auto" id="data_<?= esc($mensalidade['id'], 'attr') ?>"
           name="data_pagamento" max="<?= date('Y-m-d') ?>" value="<?= esc($dataPagamento, 'attr') ?>" required>
    <label class="visually-hidden" for="forma_<?= esc($mensalidade['id'], 'attr') ?>">Forma de pagamento</label>
    <select class="form-select form-select-sm w-auto" id="forma_<?= esc($mensalidade['id'], 'attr') ?>" name="forma_pagamento" required>
        <option value="">Forma</option>
        <?php foreach ($formas as $valor => $rotulo) : ?>
            <option value="<?= esc($valor, 'attr') ?>" <?= $formaEscolhida === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-sm btn-primary">Quitar</button>
</form>
