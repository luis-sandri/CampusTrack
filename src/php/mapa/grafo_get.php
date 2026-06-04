<?php
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$id_instituicao = mapa_ler_id_instituicao($_GET, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$grafo = mapa_grafo_buscar($conexao, $id_instituicao);

if ($grafo === null) {
    responder_json(resposta_erro("Nao foi possivel carregar o grafo."));
}

responder_json(resposta_ok("Grafo carregado.", [$grafo]));
