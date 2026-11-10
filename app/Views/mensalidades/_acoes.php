<?php
/**
 * Ações de gestão de uma mensalidade em aberto: corrigir e excluir. Só o proprietário vê (RN05).
 *
 * @var array<string, mixed> $mensalidade
 */
if (session()->get('usuario_perfil') !== 'proprietario') {
    return;
}
?>
<div class="d-flex gap-1 justify-content-end mt-1">
    <a href="<?= esc(url_to('mensalidades_editar', $mensalidade['id']), 'html') ?>" class="btn btn-sm btn-link">Editar</a>
    <form action="<?= esc(url_to('mensalidades_excluir', $mensalidade['id']), 'html') ?>" method="post"
          onsubmit="return confirm('Excluir esta mensalidade?');">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
    </form>
</div>
