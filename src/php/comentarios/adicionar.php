<?php
include_once __DIR__ . "/../core/valida_sessao_aluno.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$comentario = comentario_ler_formulario($_POST, $erros);
$id_aluno = (int) $_SESSION["aluno_id"];

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$evento = comentario_buscar_evento_avaliavel($conexao, $comentario["id_evento"]);

if ($evento === null) {
    responder_json(resposta_erro("Nao foi possivel validar o evento."));
}

if (count($evento) === 0) {
    responder_json(resposta_erro("Evento nao encontrado ou nao esta ativo."));
}

if (!comentario_evento_encerrado($evento)) {
    responder_json(resposta_erro("O evento ainda nao foi encerrado."));
}

$ja_enviou = comentario_aluno_ja_enviou($conexao, $id_aluno, $comentario["id_evento"]);

if ($ja_enviou === null) {
    responder_json(resposta_erro("Nao foi possivel verificar feedback anterior."));
}

if ($ja_enviou) {
    responder_json(resposta_erro("Voce ja enviou um feedback para este evento."));
}

if (!comentario_inserir($conexao, $id_aluno, $comentario)) {
    responder_json(resposta_erro("Nao foi possivel enviar a avaliacao."));
}

responder_json(resposta_ok("Avaliacao enviada com sucesso."));
