<?php
include_once __DIR__ . "/../core/valida_sessao_admin.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

if (isset($_GET["id"])) {
    $erros = [];
    $id_gerente = gerente_ler_id($_GET, $erros);

    if (count($erros) > 0) {
        responder_json(resposta_validacao($erros));
    }

    $gerentes = gerente_buscar_por_id($conexao, $id_gerente);

    if ($gerentes === null) {
        responder_json(resposta_erro("Nao foi possivel carregar o gerente."));
    }

    if (count($gerentes) === 0) {
        responder_json(resposta_erro("Registro nao encontrado."));
    }

    responder_json(resposta_ok("Registro encontrado.", $gerentes));
}

$gerentes = gerente_listar($conexao);
if ($gerentes === null) {
    responder_json(resposta_erro("Nao foi possivel carregar os gerentes."));
}

responder_json(resposta_ok("Lista carregada.", $gerentes));
