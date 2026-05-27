<?php
include_once __DIR__ . "/valida_sessao_organizador.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_evento = isset($_POST["id_evento"]) ? trim((string) $_POST["id_evento"]) : "";
$id_organizador = (int) $_SESSION["organizador_id"];
$id_organizacao = (int) $_SESSION["organizador_id_organizacao"];

if (!ctype_digit($id_evento) || $id_evento <= 0) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "ID de evento invalido.",
        "data" => [],
    ];
} else {
    $id_evento = (int) $id_evento;

    $stmt = $conexao->prepare(
        "DELETE FROM Evento WHERE id_evento = ? AND id_organizacao = ?"
    );
    $stmt->bind_param("ii", $id_evento, $id_organizacao);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno = [
            "status" => "ok",
            "mensagem" => "Evento excluido com sucesso.",
            "data" => [],
        ];
    } else {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Não foi possível excluir o evento. Verifique se ele pertence à sua organização.",
            "data" => [],
        ];
    }
    $stmt->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
