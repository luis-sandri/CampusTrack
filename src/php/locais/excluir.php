<?php
include_once __DIR__ . "/../core/valida_sessao_gerente.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$id_local = local_ler_id($_GET, $erros);
$id_instituicao_gerente = (int) $_SESSION["gerente_id_instituicao"];

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (!local_existe_na_instituicao($conexao, $id_local, $id_instituicao_gerente)) {
    responder_json(resposta_erro("Local nao encontrado ou sem permissao para excluir."));
}

if (!local_excluir($conexao, $id_local, $id_instituicao_gerente)) {
    responder_json(resposta_erro("Nao foi possivel excluir o registro. Verifique se existem registros vinculados."));
}

responder_json(resposta_ok("Registro excluido com sucesso."));
