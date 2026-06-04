<?php
include_once __DIR__ . "/../core/valida_sessao_gerente.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$id_local = local_ler_id($_GET, $erros);
$local = local_ler_formulario($_POST, $erros);
$id_instituicao_gerente = (int) $_SESSION["gerente_id_instituicao"];

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if ($local["id_instituicao"] !== $id_instituicao_gerente) {
    responder_json(resposta_erro("Acesso negado para esta instituicao."));
}

if (!local_existe_na_instituicao($conexao, $id_local, $id_instituicao_gerente)) {
    responder_json(resposta_erro("Local nao encontrado ou sem permissao para alterar."));
}

if (!local_atualizar($conexao, $id_local, $id_instituicao_gerente, $local)) {
    responder_json(resposta_erro("Nao foi possivel alterar o registro."));
}

responder_json(resposta_ok("Registro alterado com sucesso."));
