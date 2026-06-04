<?php
include_once __DIR__ . "/../core/valida_sessao_organizacao.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$id_organizacao = (int) $_SESSION["organizacao_id"];

if (isset($_GET["id"])) {
    $erros = [];
    $id_usuario = organizador_ler_id($_GET, $erros);

    if (count($erros) > 0) {
        responder_json(resposta_validacao($erros));
    }

    $organizadores = organizador_buscar_por_id($conexao, $id_usuario, $id_organizacao);

    if ($organizadores === null) {
        responder_json(resposta_erro("Nao foi possivel carregar o organizador."));
    }

    if (count($organizadores) === 0) {
        responder_json(resposta_erro("Registro nao encontrado."));
    }

    responder_json(resposta_ok("Registro encontrado.", $organizadores));
}

$organizadores = organizador_listar_por_organizacao($conexao, $id_organizacao);
if ($organizadores === null) {
    responder_json(resposta_erro("Nao foi possivel carregar os organizadores."));
}

responder_json(resposta_ok("Lista carregada.", $organizadores));
