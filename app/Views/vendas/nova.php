<?php
/**
 * Venda no balcão: o atendente informa a quantidade de cada produto, o aluno (opcional) e a forma de pagamento (RF27).
 *
 * @var list<array<string, mixed>> $produtos produtos ativos com estoque
 * @var list<array<string, mixed>> $alunos
 */
$categorias   = rotulos_de('categorias_produto');
$formas       = rotulos_de('formas_pagamento');
$alunoAtual   = (string) old('aluno_id', '');
$formaAtual   = (string) old('forma_pagamento', 'PIX');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header">
    <h1 class="h3 mb-0">Nova venda</h1>
    <a href="<?= esc(url_to('vendas'), 'html') ?>" class="btn btn-outline-secondary">Voltar</a>
</header>

<?php if ($produtos === []) : ?>
    <div class="alert alert-secondary" role="status">Não há produtos disponíveis (ativos e com estoque). Cadastre ou reponha em Produtos.</div>
<?php else : ?>
<form action="<?= esc(url_to('vendas_criar'), 'html') ?>" method="post" novalidate>
    <?= csrf_field() ?>

    <div class="ct-card mb-3">
    <div class="table-responsive position-relative" tabindex="0" role="region" aria-label="Produtos disponíveis para venda">
        <table class="table table-striped align-middle">
            <caption class="visually-hidden">Informe a quantidade de cada produto vendido</caption>
            <thead>
                <tr>
                    <th scope="col">Produto</th>
                    <th scope="col">Categoria</th>
                    <th scope="col" class="num">Preço</th>
                    <th scope="col" class="num">Em estoque</th>
                    <th scope="col" style="width: 8rem;">Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto) : ?>
                    <tr>
                        <td><label for="qtd_<?= esc($produto['id'], 'attr') ?>"><?= esc($produto['nome']) ?></label></td>
                        <td><?= esc($categorias[$produto['categoria']] ?? $produto['categoria']) ?></td>
                        <td class="num"><?= esc(formatar_moeda($produto['preco_venda'])) ?></td>
                        <td class="num"><?= esc((string) $produto['estoque_atual']) ?></td>
                        <td>
                            <input type="number" class="form-control form-control-sm" id="qtd_<?= esc($produto['id'], 'attr') ?>"
                                   name="quantidade[<?= esc($produto['id'], 'attr') ?>]" min="0" max="<?= esc($produto['estoque_atual'], 'attr') ?>"
                                   value="<?= esc((string) old('quantidade.' . $produto['id'], '0'), 'attr') ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>

    <div class="card card-body mb-3">
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label for="aluno_id" class="form-label">Aluno <span class="opcional">(opcional, deixe em branco para cliente avulso)</span></label>
                <select class="form-select" id="aluno_id" name="aluno_id">
                    <option value="">Cliente avulso</option>
                    <?php foreach ($alunos as $aluno) : ?>
                        <option value="<?= esc($aluno['id'], 'attr') ?>" <?= $alunoAtual === (string) $aluno['id'] ? 'selected' : '' ?>>
                            <?= esc($aluno['codigo'] . ' · ' . $aluno['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="forma_pagamento" class="form-label">Forma de pagamento</label>
                <select class="form-select" id="forma_pagamento" name="forma_pagamento" required>
                    <?php foreach ($formas as $valor => $rotulo) : ?>
                        <option value="<?= esc($valor, 'attr') ?>" <?= $formaAtual === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="ct-form-acoes">
        <button type="submit" class="btn btn-primary">Registrar venda</button>
        <a href="<?= esc(url_to('vendas'), 'html') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?php endif; ?>
<?= $this->endSection() ?>
