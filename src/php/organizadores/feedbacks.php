<?php
include_once __DIR__ . "/../core/valida_sessao_organizador.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/../comentarios/repositorio.php";

$id_organizacao = (int) $_SESSION["organizador_id_organizacao"];
$feedbacks = comentario_listar_feedbacks_por_organizacao($conexao, $id_organizacao);

if ($feedbacks === null) {
    responder_json(resposta_erro("Nao foi possivel carregar os feedbacks."));
}

responder_json(resposta_ok("Feedbacks carregados.", $feedbacks));
