<?php
include_once __DIR__ . "/../core/valida_sessao_gerente.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/repositorio.php";

$id_instituicao = isset($_SESSION["gerente_id_instituicao"]) ? (int) $_SESSION["gerente_id_instituicao"] : 0;

if ($id_instituicao <= 0) {
    responder_json(resposta_erro("Instituicao do gerente nao encontrada na sessao."));
}

$instituicoes = instituicao_buscar_por_id($conexao, $id_instituicao);

if ($instituicoes === null) {
    responder_json(resposta_erro("Nao foi possivel carregar a instituicao."));
}

if (count($instituicoes) === 0) {
    responder_json(resposta_erro("Instituicao nao encontrada."));
}

responder_json(resposta_ok("Lista carregada.", $instituicoes));
