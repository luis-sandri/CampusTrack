<?php
include_once __DIR__ . "/../core/valida_sessao_admin.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$id_instituicao = instituicao_ler_id($_GET, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (!instituicao_existe($conexao, $id_instituicao)) {
    responder_json(resposta_erro("Instituicao nao encontrada."));
}

if (!instituicao_excluir($conexao, $id_instituicao)) {
    responder_json(resposta_erro("Nao foi possivel excluir a instituicao. Verifique se existem registros vinculados."));
}

responder_json(resposta_ok("Instituicao excluida com sucesso."));
