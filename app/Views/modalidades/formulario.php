<?php
/**
 * Formulário de cadastro e edição de modalidade (RF05).
 *
 * @var array<string, mixed> $modalidade valores atuais
 * @var string               $acao       URL de destino do POST
 */
$categorias       = \App\Models\ModalidadeModel::CATEGORIAS;
$categoriaAtual   = old('categoria', $modalidade['categoria'] ?? '');
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<h1 class="h3 mb-3"><?= esc($titulo) ?></h1>

<form action="<?= esc($acao, 'attr') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <div class="card card-body mb-3">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="nome" class="form-label">Nome</label>
                <input type="text" class="form-control" id="nome" name="nome" maxlength="80" required
                       value="<?= esc(old('nome', $modalidade['nome'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-3">
                <label for="categoria" class="form-label">Categoria</label>
                <select class="form-select" id="categoria" name="categoria" required>
                    <option value="">Selecione</option>
                    <?php foreach ($categorias as $valor => $rotulo) : ?>
                        <option value="<?= esc($valor, 'attr') ?>" <?= $categoriaAtual === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="capacidade_padrao" class="form-label">Capacidade padrão</label>
                <input type="number" class="form-control" id="capacidade_padrao" name="capacidade_padrao" min="1" required
                       aria-describedby="ajudaCapacidade"
                       value="<?= esc(old('capacidade_padrao', (string) ($modalidade['capacidade_padrao'] ?? ''))) ?>">
                <div id="ajudaCapacidade" class="form-text">Lutas: até 20 alunos. Esportes coletivos: até 25.</div>
            </div>
            <div class="col-12">
                <label for="descricao" class="form-label">Descrição <span class="text-muted">(opcional)</span></label>
                <textarea class="form-control" id="descricao" name="descricao" rows="2" maxlength="1000"><?= esc(old('descricao', $modalidade['descricao'] ?? '')) ?></textarea>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= url_to('modalidades') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
