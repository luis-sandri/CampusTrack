<?php
include_once __DIR__ . "/valida_sessao_gerente.php";
include_once __DIR__ . "/conexao.php";
require_once __DIR__ . "/core/resposta.php";
require_once __DIR__ . "/eventos/validacoes.php";
require_once __DIR__ . "/eventos/repositorio.php";

$erros = [];
$id_evento = evento_ler_id($_POST, $erros);
$novo_status = evento_ler_status($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$id_instituicao = (int) $_SESSION["gerente_id_instituicao"];

$evento_valido = evento_pendente_pertence_instituicao($conexao, $id_evento, $id_instituicao);

if ($evento_valido === null) {
    responder_json(resposta_erro("Nao foi possivel validar o evento."));
}

if (!$evento_valido) {
    responder_json(resposta_erro("Evento nao encontrado ou sem permissao para alterar."));
}

if (!evento_alterar_status($conexao, $id_evento, $novo_status)) {
    responder_json(resposta_erro("Nao foi possivel alterar o status do evento."));
}

$mensagem = $novo_status === "ativo" ? "Evento aprovado com sucesso." : "Evento recusado com sucesso.";
responder_json(resposta_ok($mensagem));
