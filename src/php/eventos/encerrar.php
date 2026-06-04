<?php
include_once __DIR__ . "/../core/valida_sessao_organizador.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$id_evento = evento_ler_id($_POST, $erros);
$id_organizacao = (int) $_SESSION["organizador_id_organizacao"];

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (!evento_encerrar_por_organizacao($conexao, $id_evento, $id_organizacao)) {
    responder_json(resposta_erro("Nao foi possivel encerrar o evento. Verifique se ele esta ativo e pertence a sua organizacao."));
}

responder_json(resposta_ok("Evento encerrado com sucesso."));
