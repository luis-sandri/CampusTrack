<?php
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/repositorio.php";
require_once __DIR__ . "/listagem_funcoes.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

$id_instituicao = evento_obter_id_instituicao_filtro($_GET, $_SESSION);

if ((isset($_GET["id"]) || isset($_GET["id_instituicao"])) && $id_instituicao === null) {
    responder_json(resposta_erro("Instituicao invalida."));
}

$eventos = evento_listar_publicos($conexao, (int) $id_instituicao);

if ($eventos === null) {
    responder_json(resposta_erro("Nao foi possivel carregar os eventos."));
}

responder_json(resposta_ok("Lista carregada.", $eventos));
