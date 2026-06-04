<?php
include_once __DIR__ . "/../core/valida_sessao_admin.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$id_no = mapa_no_ler_id($_GET, $erros);
$no = mapa_no_ler_formulario($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$no_existe = mapa_no_pertence_instituicao($conexao, $id_no, $no["id_instituicao"]);

if ($no_existe === null) {
    responder_json(resposta_erro("Nao foi possivel validar o no."));
}

if (!$no_existe) {
    responder_json(resposta_erro("No nao encontrado."));
}

if (!mapa_no_atualizar($conexao, $id_no, $no)) {
    responder_json(resposta_erro("Nao foi possivel alterar o no."));
}

responder_json(resposta_ok("No alterado com sucesso."));
