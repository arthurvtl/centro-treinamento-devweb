<?php
/**
 * Formulário de cadastro e edição de produto (RF26).
 *
 * @var array<string, mixed> $produto
 * @var string               $acao
 */
$categorias     = rotulos_de('categorias_produto');
$categoriaAtual = old('categoria', $produto['categoria'] ?? '');
$dinheiro       = static fn (string $campo): string => (string) old($campo, isset($produto[$campo]) ? number_format((float) $produto[$campo], 2, ',', '') : '');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header"><h1 class="h3 mb-0"><?= esc($titulo) ?></h1></header>

<form action="<?= esc($acao, 'html') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="card card-body mb-3">
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label for="nome" class="form-label">Nome do produto</label>
                <input type="text" class="form-control" id="nome" name="nome" maxlength="80" required
                       value="<?= esc(old('nome', $produto['nome'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-4">
                <label for="categoria" class="form-label">Categoria</label>
                <select class="form-select" id="categoria" name="categoria" required>
                    <option value="">Selecione</option>
                    <?php foreach ($categorias as $valor => $rotulo) : ?>
                        <option value="<?= esc($valor, 'attr') ?>" <?= $categoriaAtual === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label for="preco_custo" class="form-label">Custo (R$)</label>
                <input type="text" class="form-control" id="preco_custo" name="preco_custo" inputmode="decimal" required
                       value="<?= esc($dinheiro('preco_custo')) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="preco_venda" class="form-label">Preço de venda (R$)</label>
                <input type="text" class="form-control" id="preco_venda" name="preco_venda" inputmode="decimal" required
                       value="<?= esc($dinheiro('preco_venda')) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="estoque_atual" class="form-label">Estoque atual</label>
                <input type="number" min="0" class="form-control" id="estoque_atual" name="estoque_atual" required
                       value="<?= esc((string) old('estoque_atual', $produto['estoque_atual'] ?? '0')) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="estoque_minimo" class="form-label">Estoque mínimo</label>
                <input type="number" min="0" class="form-control" id="estoque_minimo" name="estoque_minimo" required
                       value="<?= esc((string) old('estoque_minimo', $produto['estoque_minimo'] ?? '0')) ?>">
            </div>
        </div>
    </div>
    <div class="ct-form-acoes">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= esc(url_to('produtos'), 'html') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
