<?php
include_once __DIR__ . "/../core/valida_sessao_admin.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/../instituicoes/repositorio.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$no = mapa_no_ler_formulario($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (!instituicao_existe($conexao, $no["id_instituicao"])) {
    responder_json(resposta_erro("Instituicao nao encontrada."));
}

$id_no = mapa_no_inserir($conexao, $no);

if ($id_no <= 0) {
    responder_json(resposta_erro("Nao foi possivel cadastrar o no."));
}

responder_json(resposta_ok("No cadastrado com sucesso.", [["id_no" => $id_no]]));
