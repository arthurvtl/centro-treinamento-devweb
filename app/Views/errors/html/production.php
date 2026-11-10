<?php
// Não expõe exceções, consultas ou credenciais na resposta de produção.
$titulo = 'Não foi possível concluir a solicitação';
$descricao = 'Tente novamente em instantes. Se o problema continuar, avise o responsável pelo sistema.';
require __DIR__ . '/mensagem.php';
