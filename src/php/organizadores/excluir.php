<?php
include_once __DIR__ . "/../core/valida_sessao_organizacao.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$id_organizacao = (int) $_SESSION["organizacao_id"];
$erros = [];
$id_usuario = organizador_ler_id($_GET, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (!organizador_existe_na_organizacao($conexao, $id_usuario, $id_organizacao)) {
    responder_json(resposta_erro("Organizador nao encontrado."));
}

if (!organizador_excluir($conexao, $id_usuario)) {
    responder_json(resposta_erro("Nao foi possivel excluir o organizador. Verifique se existem registros vinculados."));
}

responder_json(resposta_ok("Organizador excluido com sucesso."));
