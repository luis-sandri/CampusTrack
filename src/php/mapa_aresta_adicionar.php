<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";
require_once __DIR__ . "/core/resposta.php";
require_once __DIR__ . "/mapa/validacoes.php";
require_once __DIR__ . "/mapa/repositorio.php";

$erros = [];
$aresta = mapa_aresta_ler_formulario($_POST, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$nos = mapa_buscar_nos_por_ids($conexao, $aresta["id_instituicao"], $aresta["id_no_origem"], $aresta["id_no_destino"]);

if ($nos === null) {
    responder_json(resposta_erro("Nao foi possivel validar os pontos da conexao."));
}

if (count($nos) !== 2) {
    responder_json(resposta_erro("Origem e destino precisam pertencer a instituicao selecionada."));
}

$conexao_existe = mapa_aresta_existe_conexao($conexao, $aresta);

if ($conexao_existe === null) {
    responder_json(resposta_erro("Nao foi possivel verificar se a conexao ja existe."));
}

if ($conexao_existe) {
    responder_json(resposta_erro("Esta conexao ja existe."));
}

$distancia = mapa_distancia_metros(
    (float) $nos[$aresta["id_no_origem"]]["latitude"],
    (float) $nos[$aresta["id_no_origem"]]["longitude"],
    (float) $nos[$aresta["id_no_destino"]]["latitude"],
    (float) $nos[$aresta["id_no_destino"]]["longitude"]
);
$id_aresta = mapa_aresta_inserir($conexao, $aresta, $distancia);

if ($id_aresta <= 0) {
    responder_json(resposta_erro("Nao foi possivel cadastrar a conexao."));
}

responder_json(resposta_ok("Conexao cadastrada com sucesso.", [["id_aresta" => $id_aresta, "distancia_metros" => $distancia]]));
