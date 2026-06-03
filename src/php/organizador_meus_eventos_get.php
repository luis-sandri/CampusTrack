<?php
include_once __DIR__ . "/valida_sessao_organizador.php";
include_once __DIR__ . "/conexao.php";
require_once __DIR__ . "/core/resposta.php";
require_once __DIR__ . "/eventos/repositorio.php";

$id_organizador = (int) $_SESSION["organizador_id"];
$eventos = evento_listar_por_organizador($conexao, $id_organizador);

if ($eventos === null) {
    responder_json(resposta_erro("Nao foi possivel carregar os eventos."));
}

responder_json(resposta_ok("Lista carregada.", $eventos));
