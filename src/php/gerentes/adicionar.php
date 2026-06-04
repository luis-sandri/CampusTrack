<?php
include_once __DIR__ . "/../core/valida_sessao_admin.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/../instituicoes/repositorio.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$gerente = gerente_ler_cadastro($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (usuario_email_em_uso($conexao, $gerente["email"])) {
    responder_json(resposta_erro("Ja existe um usuario cadastrado com este e-mail."));
}

if (!instituicao_existe($conexao, $gerente["id_instituicao"])) {
    responder_json(resposta_erro("Instituicao nao encontrada."));
}

$id_usuario = gerente_inserir($conexao, $gerente);

if ($id_usuario <= 0) {
    responder_json(resposta_erro("Nao foi possivel cadastrar o gerente."));
}

responder_json(resposta_ok("Gerente cadastrado com sucesso.", [["id_usuario" => $id_usuario]]));
