<?php
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$organizacao = organizacao_ler_cadastro($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$cnpj_em_uso = organizacao_cnpj_em_uso($conexao, $organizacao["cnpj"]);

if ($cnpj_em_uso === null) {
    responder_json(resposta_erro("Nao foi possivel verificar o CNPJ."));
}

if ($cnpj_em_uso) {
    responder_json(resposta_erro("Ja existe uma organizacao cadastrada com este CNPJ."));
}

$id_organizacao = organizacao_inserir($conexao, $organizacao);

if ($id_organizacao <= 0) {
    responder_json(resposta_erro("Nao foi possivel cadastrar a organizacao."));
}

responder_json(resposta_ok("Organizacao cadastrada com sucesso.", [["id_organizacao" => $id_organizacao]]));
