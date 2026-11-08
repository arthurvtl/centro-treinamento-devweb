<?php
/**
 * Ficha do aluno. A condição de saúde só chega nesta View para o proprietário (RNF06).
 *
 * @var array<string, mixed>      $aluno
 * @var int                       $idade
 * @var array<string, mixed>|null $responsavel
 */
$ehProprietario = session()->get('usuario_perfil') === 'proprietario';
$ativo          = $aluno['situacao'] === 'ATIVO';
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0"><?= esc($aluno['nome']) ?>
        <span class="badge <?= $ativo ? 'text-bg-success' : 'text-bg-secondary' ?> fs-6"><?= $ativo ? 'Ativo' : 'Inativo' ?></span>
    </h1>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= esc(url_to('alunos_editar', $aluno['id']), 'html') ?>" class="btn btn-outline-primary">Editar</a>
        <a href="<?= esc(url_to('mensalidades_aluno', $aluno['id']), 'html') ?>" class="btn btn-outline-success">Financeiro</a>
        <form action="<?= esc(url_to($ativo ? 'alunos_inativar' : 'alunos_reativar', $aluno['id']), 'html') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-warning"><?= $ativo ? 'Inativar' : 'Reativar' ?></button>
        </form>
        <?php if ($ehProprietario) : ?>
            <form action="<?= esc(url_to('alunos_excluir', $aluno['id']), 'html') ?>" method="post"
                  onsubmit="return confirm('Excluir este aluno definitivamente?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-outline-danger">Excluir</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<section class="card card-body mb-4" aria-labelledby="tituloDados">
    <h2 class="h5" id="tituloDados">Dados do aluno</h2>
    <dl class="row mb-0">
        <dt class="col-sm-3">Código</dt><dd class="col-sm-9"><?= esc($aluno['codigo']) ?></dd>
        <dt class="col-sm-3">Nascimento</dt><dd class="col-sm-9"><?= esc(formatar_data($aluno['data_nascimento'])) ?> (<?= esc((string) $idade) ?> anos)</dd>
        <dt class="col-sm-3">CPF</dt><dd class="col-sm-9"><?= esc(formatar_cpf($aluno['cpf'])) ?></dd>
        <dt class="col-sm-3">Telefone</dt><dd class="col-sm-9"><?= esc(formatar_telefone($aluno['telefone'])) ?></dd>
        <dt class="col-sm-3">E-mail</dt><dd class="col-sm-9"><?= esc($aluno['email'] ?: '—') ?></dd>
        <dt class="col-sm-3">Endereço</dt><dd class="col-sm-9"><?= esc($aluno['endereco']) ?></dd>
        <dt class="col-sm-3">Matrícula em</dt><dd class="col-sm-9"><?= esc(formatar_data($aluno['data_matricula'])) ?></dd>
        <dt class="col-sm-3">Contato de emergência</dt><dd class="col-sm-9"><?= esc($aluno['contato_emergencia']) ?></dd>
        <?php if ($ehProprietario) : ?>
            <dt class="col-sm-3">Condição de saúde</dt><dd class="col-sm-9"><?= esc($aluno['condicao_saude'] ?: '—') ?></dd>
        <?php endif; ?>
    </dl>
</section>

<?php if ($responsavel !== null) : ?>
    <section class="card card-body mb-4" aria-labelledby="tituloResponsavel">
        <h2 class="h5" id="tituloResponsavel">Responsável legal</h2>
        <dl class="row mb-0">
            <dt class="col-sm-3">Nome</dt><dd class="col-sm-9"><?= esc($responsavel['nome']) ?> (<?= esc($responsavel['parentesco']) ?>)</dd>
            <dt class="col-sm-3">CPF</dt><dd class="col-sm-9"><?= esc(formatar_cpf($responsavel['cpf'])) ?></dd>
            <dt class="col-sm-3">Telefone</dt><dd class="col-sm-9"><?= esc(formatar_telefone($responsavel['telefone'])) ?></dd>
            <dt class="col-sm-3">E-mail</dt><dd class="col-sm-9"><?= esc($responsavel['email'] ?: '—') ?></dd>
        </dl>
    </section>
<?php endif; ?>

<a href="<?= esc(url_to('alunos'), 'html') ?>" class="btn btn-link px-0">Voltar para a lista</a>
<?= $this->endSection() ?>
