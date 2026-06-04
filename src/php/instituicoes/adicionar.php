<?php
include_once __DIR__ . "/../core/valida_sessao_admin.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$instituicao = instituicao_ler_formulario($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$id_instituicao = instituicao_inserir($conexao, $instituicao);

if ($id_instituicao <= 0) {
    responder_json(resposta_erro("Nao foi possivel cadastrar a instituicao."));
}

responder_json(resposta_ok("Instituicao cadastrada com sucesso.", [["id_instituicao" => $id_instituicao]]));
