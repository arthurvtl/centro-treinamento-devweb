<?php
/**
 * Modelo de paginação em português, com as classes do Bootstrap 5 (substitui o `default_full` do CodeIgniter).
 *
 * As Views das listas envolvem a chamada `$pager->links()` num <nav> com o rótulo de cada lista,
 * por isso aqui só sai a lista de páginas.
 *
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>
<ul class="pagination mb-0">
    <?php if ($pager->hasPreviousPage()) : ?>
        <li class="page-item"><a class="page-link" href="<?= esc($pager->getFirst(), 'html') ?>">Primeira</a></li>
        <li class="page-item"><a class="page-link" href="<?= esc($pager->getPreviousPage(), 'html') ?>">Anterior</a></li>
    <?php endif; ?>

    <?php foreach ($pager->links() as $link) : ?>
        <li class="page-item<?= $link['active'] ? ' active' : '' ?>">
            <a class="page-link" href="<?= esc($link['uri'], 'html') ?>"<?= $link['active'] ? ' aria-current="page"' : '' ?>><?= esc($link['title']) ?></a>
        </li>
    <?php endforeach; ?>

    <?php if ($pager->hasNextPage()) : ?>
        <li class="page-item"><a class="page-link" href="<?= esc($pager->getNextPage(), 'html') ?>">Próxima</a></li>
        <li class="page-item"><a class="page-link" href="<?= esc($pager->getLast(), 'html') ?>">Última</a></li>
    <?php endif; ?>
</ul>
