<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";
require_once __DIR__ . "/core/resposta.php";
require_once __DIR__ . "/instituicoes/repositorio.php";
require_once __DIR__ . "/gerentes/validacoes.php";
require_once __DIR__ . "/gerentes/repositorio.php";

$erros = [];
$id_gerente = gerente_ler_id($_GET, $erros);
$gerente = gerente_ler_alteracao($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (!gerente_existe($conexao, $id_gerente)) {
    responder_json(resposta_erro("Gerente nao encontrado."));
}

if (usuario_email_em_uso($conexao, $gerente["email"], $id_gerente)) {
    responder_json(resposta_erro("Ja existe um usuario cadastrado com este e-mail."));
}

if (!instituicao_existe($conexao, $gerente["id_instituicao"])) {
    responder_json(resposta_erro("Instituicao nao encontrada."));
}

if (!gerente_atualizar($conexao, $id_gerente, $gerente)) {
    responder_json(resposta_erro("Nao foi possivel alterar o gerente."));
}

responder_json(resposta_ok("Gerente alterado com sucesso."));
