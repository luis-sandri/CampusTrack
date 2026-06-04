<?php
include_once __DIR__ . "/../core/valida_sessao_aluno.php";
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/repositorio.php";

$id_aluno = (int) $_SESSION["aluno_id"];
$favoritos = favorito_listar_por_aluno($conexao, $id_aluno);

if ($favoritos === null) {
    responder_json(resposta_erro("Nao foi possivel carregar os favoritos."));
}

responder_json(resposta_ok("Lista carregada.", $favoritos));
