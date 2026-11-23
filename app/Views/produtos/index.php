<?php
/**
 * Catálogo e estoque da loja (RF26).
 *
 * @var list<array<string, mixed>> $produtos
 */
$categorias = rotulos_de('categorias_produto');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Produtos</h1>
    <div class="d-flex gap-2">
        <a href="<?= esc(url_to('vendas'), 'html') ?>" class="btn btn-outline-secondary">Vendas</a>
        <a href="<?= esc(url_to('produtos_novo'), 'html') ?>" class="btn btn-primary">Novo produto</a>
    </div>
</header>

<div class="ct-card mb-3">
<div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Lista de produtos">
    <table class="table table-striped table-hover align-middle">
        <caption class="visually-hidden">Produtos da loja</caption>
        <thead>
            <tr>
                <th scope="col">Produto</th>
                <th scope="col">Categoria</th>
                <th scope="col" class="num">Preço</th>
                <th scope="col" class="num">Estoque</th>
                <th scope="col">Situação</th>
                <th scope="col"><span class="visually-hidden">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($produtos === []) : ?>
                <tr><td colspan="6"><div class="ct-vazio">
                    <p class="mb-1 fw-semibold">Nenhum produto cadastrado.</p>
                    <a href="<?= esc(url_to('produtos_novo'), 'html') ?>" class="btn btn-outline-secondary btn-sm">Cadastrar produto</a>
                </div></td></tr>
            <?php endif; ?>
            <?php foreach ($produtos as $produto) : ?>
                <?php
                $ativo = $produto['situacao'] === 'ATIVO';
                $baixo = $ativo && (int) $produto['estoque_atual'] <= (int) $produto['estoque_minimo'];
                ?>
                <tr>
                    <td><?= esc($produto['nome']) ?></td>
                    <td><?= esc($categorias[$produto['categoria']] ?? $produto['categoria']) ?></td>
                    <td class="num"><?= esc(formatar_moeda($produto['preco_venda'])) ?></td>
                    <td class="num">
                        <?= esc((string) $produto['estoque_atual']) ?>
                        <?php if ($baixo) : ?><span class="ct-status ct-status--alerta">Repor</span><?php endif; ?>
                    </td>
                    <td><span class="ct-status <?= $ativo ? 'ct-status--ok' : 'ct-status--inativo' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= esc(url_to('produtos_editar', $produto['id']), 'html') ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <form action="<?= esc(url_to($ativo ? 'produtos_inativar' : 'produtos_reativar', $produto['id']), 'html') ?>" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-secondary"><?= $ativo ? 'Inativar' : 'Reativar' ?></button>
                        </form>
                        <?php if (session()->get('usuario_perfil') === 'proprietario') : ?>
                            <form action="<?= esc(url_to('produtos_excluir', $produto['id']), 'html') ?>" method="post" class="d-inline"
                                  onsubmit="return confirm('Excluir este produto?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</div>
<?= $this->endSection() ?>
