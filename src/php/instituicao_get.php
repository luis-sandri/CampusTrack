<?php
include_once __DIR__ . "/conexao.php";
require_once __DIR__ . "/core/resposta.php";
require_once __DIR__ . "/instituicoes/validacoes.php";
require_once __DIR__ . "/instituicoes/repositorio.php";

if (isset($_GET["id"])) {
    $erros = [];
    $id_instituicao = instituicao_ler_id($_GET, $erros);

    if (count($erros) > 0) {
        responder_json(resposta_validacao($erros));
    }

    $instituicoes = instituicao_buscar_por_id($conexao, $id_instituicao);

    if ($instituicoes === null) {
        responder_json(resposta_erro("Nao foi possivel carregar a instituicao."));
    }

    if (count($instituicoes) === 0) {
        responder_json(resposta_erro("Registro nao encontrado."));
    }

    responder_json(resposta_ok("Registro encontrado.", $instituicoes));
}

$instituicoes = instituicao_listar($conexao);
if ($instituicoes === null) {
    responder_json(resposta_erro("Nao foi possivel carregar as instituicoes."));
}

responder_json(resposta_ok("Lista carregada.", $instituicoes));
