<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";
require_once __DIR__ . "/core/resposta.php";
require_once __DIR__ . "/mapa/validacoes.php";
require_once __DIR__ . "/mapa/repositorio.php";

$erros = [];
$id_aresta = mapa_aresta_ler_id($_GET, $erros);
$id_instituicao = mapa_ler_id_instituicao($_GET, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

if (!mapa_aresta_excluir($conexao, $id_aresta, $id_instituicao)) {
    responder_json(resposta_erro("Nao foi possivel excluir a conexao."));
}

responder_json(resposta_ok("Conexao excluida com sucesso."));
