<?php
include_once __DIR__ . "/../core/valida_sessao_gerente.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/repositorio.php";

$id_instituicao = (int) $_SESSION["gerente_id_instituicao"];
$eventos = evento_listar_pendentes_por_instituicao($conexao, $id_instituicao);

if ($eventos === null) {
    responder_json(resposta_erro("Nao foi possivel carregar os eventos pendentes."));
}

responder_json(resposta_ok("Lista carregada.", $eventos));
