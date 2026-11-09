<?php
/**
 * Cartão de indicador monocromático: rótulo pequeno, número grande e legenda opcional.
 *
 * @var string      $id      identificador único do título (liga o cartão ao rótulo, para leitores de tela)
 * @var string      $rotulo  nome do indicador
 * @var string      $valor   valor já formatado
 * @var string|null $legenda texto de apoio, opcional
 * @var bool|null   $alerta  quando verdadeiro, destaca o cartão com faixa lateral (sem cor nova)
 */
?>
<section class="ct-kpi<?= ($alerta ?? false) ? ' ct-kpi--alerta' : '' ?>" aria-labelledby="<?= esc($id, 'attr') ?>">
    <h2 class="ct-kpi-rotulo" id="<?= esc($id, 'attr') ?>"><?= esc($rotulo) ?></h2>
    <p class="ct-kpi-valor"><?= esc($valor) ?></p>
    <?php if (! empty($legenda)) : ?>
        <p class="ct-kpi-legenda"><?= esc($legenda) ?></p>
    <?php endif; ?>
</section>