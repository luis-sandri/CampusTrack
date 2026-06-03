<?php
include_once __DIR__ . "/valida_sessao_organizador.php";
include_once __DIR__ . "/conexao.php";
require_once __DIR__ . "/core/resposta.php";
require_once __DIR__ . "/eventos/validacoes.php";
require_once __DIR__ . "/eventos/repositorio.php";

$erros = [];
$evento = evento_ler_formulario($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$id_organizador = (int) $_SESSION["organizador_id"];
$id_organizacao = (int) $_SESSION["organizador_id_organizacao"];

if (strtotime($evento["data"]) <= time()) {
    responder_json(resposta_erro("A data e horario do evento devem ser no futuro."));
}

$local_pertence = evento_local_pertence_instituicao($conexao, $evento["id_local"], $evento["id_instituicao"]);

if ($local_pertence === null) {
    responder_json(resposta_erro("Nao foi possivel validar o local do evento."));
}

if (!$local_pertence) {
    responder_json(resposta_erro("Local invalido para a instituicao selecionada."));
}

$tem_conflito = evento_tem_conflito_local($conexao, $evento["id_local"], $evento["data"]);

if ($tem_conflito === null) {
    responder_json(resposta_erro("Nao foi possivel verificar a disponibilidade do local."));
}

if ($tem_conflito) {
    responder_json(resposta_erro("Local indisponivel para o periodo selecionado."));
}

$id_evento = evento_inserir($conexao, $evento, $id_organizacao, $id_organizador);

if ($id_evento <= 0) {
    responder_json(resposta_erro("Nao foi possivel cadastrar o evento."));
}

responder_json(resposta_ok("Evento solicitado com sucesso.", [["id_evento" => $id_evento]]));
