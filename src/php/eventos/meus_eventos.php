<?php
include_once __DIR__ . "/../core/valida_sessao_organizador.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/repositorio.php";

$id_organizador = (int) $_SESSION["organizador_id"];
$eventos = evento_listar_por_organizador($conexao, $id_organizador);

if ($eventos === null) {
    responder_json(resposta_erro("Nao foi possivel carregar os eventos."));
}

responder_json(resposta_ok("Lista carregada.", $eventos));
