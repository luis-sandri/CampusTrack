<?php
include_once __DIR__ . "/../core/valida_sessao_admin.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$id_no = mapa_no_ler_id($_GET, $erros);
$id_instituicao = mapa_ler_id_instituicao($_GET, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$no_existe = mapa_no_pertence_instituicao($conexao, $id_no, $id_instituicao);

if ($no_existe === null) {
    responder_json(resposta_erro("Nao foi possivel validar o no."));
}

if (!$no_existe) {
    responder_json(resposta_erro("No nao encontrado."));
}

if (!mapa_no_excluir($conexao, $id_no, $id_instituicao)) {
    responder_json(resposta_erro("Nao foi possivel excluir o no. Verifique se existem conexoes vinculadas."));
}

responder_json(resposta_ok("No excluido com sucesso."));
