<?php
include_once __DIR__ . "/../core/valida_sessao_admin.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$id_gerente = gerente_ler_id($_GET, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (!gerente_existe($conexao, $id_gerente)) {
    responder_json(resposta_erro("Gerente nao encontrado."));
}

if (!gerente_excluir($conexao, $id_gerente)) {
    responder_json(resposta_erro("Nao foi possivel excluir o gerente. Verifique se existem registros vinculados."));
}

responder_json(resposta_ok("Gerente excluido com sucesso."));
