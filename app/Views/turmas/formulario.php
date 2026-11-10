<?php
/**
 * Formulário de cadastro e edição de turma (RF06). Professor e limite são conferidos no servidor (RN01-RN03).
 *
 * @var array<string, mixed>       $turma
 * @var list<array<string, mixed>> $modalidades
 * @var list<array<string, mixed>> $professores
 * @var string                     $acao
 */
$diasSemana     = rotulos_de('dias_semana');
$diasMarcados   = (array) old('dias_semana', isset($turma['dias_semana']) ? explode(',', $turma['dias_semana']) : []);
$modalidadeAtual = (int) old('modalidade_id', $turma['modalidade_id'] ?? 0);
$professorAtual  = (int) old('professor_id', $turma['professor_id'] ?? 0);
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('conteudo') ?>
<header class="ct-page-header"><h1 class="h3 mb-0"><?= esc($titulo) ?></h1></header>

<form action="<?= esc($acao, 'html') ?>" method="post" novalidate>
    <?= csrf_field() ?>
    <fieldset class="card card-body mb-4">
        <legend class="h5">Dados da turma</legend>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="nome" class="form-label">Nome da turma</label>
                <input type="text" class="form-control" id="nome" name="nome" maxlength="80" required
                       value="<?= esc(old('nome', $turma['nome'] ?? '')) ?>">
            </div>
            <div class="col-12 col-md-3">
                <label for="modalidade_id" class="form-label">Modalidade</label>
                <select class="form-select" id="modalidade_id" name="modalidade_id" required>
                    <option value="">Selecione</option>
                    <?php foreach ($modalidades as $modalidade) : ?>
                        <option value="<?= esc($modalidade['id'], 'attr') ?>" <?= $modalidadeAtual === (int) $modalidade['id'] ? 'selected' : '' ?>>
                            <?= esc($modalidade['nome']) ?> (até <?= esc((string) $modalidade['capacidade_padrao']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label for="professor_id" class="form-label">Professor</label>
                <select class="form-select" id="professor_id" name="professor_id" required aria-describedby="ajudaProfessor">
                    <option value="">Selecione</option>
                    <?php foreach ($professores as $professor) : ?>
                        <option value="<?= esc($professor['id'], 'attr') ?>" <?= $professorAtual === (int) $professor['id'] ? 'selected' : '' ?>><?= esc($professor['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="ajudaProfessor" class="form-text">Precisa estar habilitado na modalidade.</div>
            </div>
            <div class="col-12">
                <span class="form-label d-block" id="rotuloDias">Dias da semana</span>
                <div role="group" aria-labelledby="rotuloDias">
                    <?php foreach ($diasSemana as $sigla => $rotulo) : ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="dias_semana[]" id="dia_<?= esc($sigla, 'attr') ?>"
                                   value="<?= esc($sigla, 'attr') ?>" <?= in_array($sigla, $diasMarcados, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="dia_<?= esc($sigla, 'attr') ?>"><?= esc($rotulo) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <label for="hora_inicio" class="form-label">Início</label>
                <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required
                       value="<?= esc(substr((string) old('hora_inicio', $turma['hora_inicio'] ?? ''), 0, 5)) ?>">
            </div>
            <div class="col-6 col-md-3">
                <label for="hora_termino" class="form-label">Término</label>
                <input type="time" class="form-control" id="hora_termino" name="hora_termino" required
                       value="<?= esc(substr((string) old('hora_termino', $turma['hora_termino'] ?? ''), 0, 5)) ?>">
            </div>
            <div class="col-12 col-md-2">
                <label for="limite_vagas" class="form-label">Limite de vagas</label>
                <input type="number" class="form-control" id="limite_vagas" name="limite_vagas" min="1" required
                       value="<?= esc((string) old('limite_vagas', (string) ($turma['limite_vagas'] ?? ''))) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label for="data_inicio" class="form-label">Data de início</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required
                       value="<?= esc(old('data_inicio', $turma['data_inicio'] ?? '')) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label for="data_termino" class="form-label">Data de término <span class="opcional">(opcional)</span></label>
                <input type="date" class="form-control" id="data_termino" name="data_termino"
                       value="<?= esc(old('data_termino', $turma['data_termino'] ?? '')) ?>">
            </div>
        </div>
    </fieldset>
    <div class="ct-form-acoes">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="<?= esc(url_to('turmas'), 'html') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
<?= $this->endSection() ?>
