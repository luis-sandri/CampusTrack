<?php
include_once __DIR__ . "/../core/valida_sessao_gerente.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$local = local_ler_formulario($_POST, $erros);
$id_instituicao_gerente = (int) $_SESSION["gerente_id_instituicao"];

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if ($local["id_instituicao"] !== $id_instituicao_gerente) {
    responder_json(resposta_erro("Acesso negado para esta instituicao."));
}

$id_local = local_inserir($conexao, $local);

if ($id_local <= 0) {
    responder_json(resposta_erro("Nao foi possivel cadastrar o registro."));
}

responder_json(resposta_ok("Registro cadastrado com sucesso.", [["id_local" => $id_local]]));
