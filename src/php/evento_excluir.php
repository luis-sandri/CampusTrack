<?php
include_once __DIR__ . "/valida_sessao_organizador.php";
include_once __DIR__ . "/conexao.php";
require_once __DIR__ . "/core/resposta.php";
require_once __DIR__ . "/eventos/validacoes.php";
require_once __DIR__ . "/eventos/repositorio.php";

$erros = [];
$id_evento = evento_ler_id($_POST, $erros);
$id_organizacao = (int) $_SESSION["organizador_id_organizacao"];

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (!evento_excluir_por_organizacao($conexao, $id_evento, $id_organizacao)) {
    responder_json(resposta_erro("Nao foi possivel excluir o evento. Verifique se ele pertence a sua organizacao."));
}

responder_json(resposta_ok("Evento excluido com sucesso."));
