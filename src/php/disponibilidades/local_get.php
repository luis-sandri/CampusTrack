<?php
include_once __DIR__ . "/../core/conexao.php";
include_once __DIR__ . "/../core/valida_sessao_gerente.php";
require_once __DIR__ . "/../core/resposta.php";
require_once __DIR__ . "/validacoes.php";
require_once __DIR__ . "/repositorio.php";

$erros = [];
$data = disponibilidade_ler_data($_GET, $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$id_instituicao = (int) $_SESSION["gerente_id_instituicao"];
$eventos = disponibilidade_listar_ocupados($conexao, $id_instituicao, $data);

if ($eventos === null) {
    responder_json(resposta_erro("Nao foi possivel consultar a disponibilidade."));
}

$mensagem = count($eventos) > 0 ? "Lista carregada." : "Nenhum espaco disponivel para o periodo selecionado.";
responder_json(resposta_ok($mensagem, $eventos));
