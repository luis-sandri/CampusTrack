<?php
include_once __DIR__ . "/conexao.php";
require_once __DIR__ . "/core/resposta.php";
require_once __DIR__ . "/locais/validacoes.php";
require_once __DIR__ . "/locais/repositorio.php";

if (isset($_GET["id_instituicao"])) {
    $erros = [];
    $id_instituicao = campo_inteiro_positivo_obrigatorio($_GET, "id_instituicao", "Instituicao", $erros);

    if (count($erros) > 0) {
        responder_json(resposta_validacao($erros));
    }

    $locais = local_listar_por_instituicao($conexao, $id_instituicao);

    if ($locais === null) {
        responder_json(resposta_erro("Nao foi possivel carregar os locais."));
    }

    responder_json(resposta_ok("Lista carregada.", $locais));
}

if (isset($_GET["gerente"])) {
    include_once __DIR__ . "/valida_sessao_gerente.php";

    $id_instituicao = (int) $_SESSION["gerente_id_instituicao"];
    $locais = local_listar_por_instituicao($conexao, $id_instituicao, "L.id_local");

    if ($locais === null) {
        responder_json(resposta_erro("Nao foi possivel carregar os locais."));
    }

    responder_json(resposta_ok("Lista carregada.", $locais));
}

if (isset($_GET["id"])) {
    $erros = [];
    $id_local = local_ler_id($_GET, $erros);

    if (count($erros) > 0) {
        responder_json(resposta_validacao($erros));
    }

    $locais = local_buscar_por_id($conexao, $id_local);

    if ($locais === null) {
        responder_json(resposta_erro("Nao foi possivel carregar o local."));
    }

    if (count($locais) === 0) {
        responder_json(resposta_erro("Registro nao encontrado."));
    }

    responder_json(resposta_ok("Registro encontrado.", $locais));
}

$locais = local_listar_todos($conexao);

if ($locais === null) {
    responder_json(resposta_erro("Nao foi possivel carregar os locais."));
}

responder_json(resposta_ok("Lista carregada.", $locais));
