<?php
include_once __DIR__ . "/valida_sessao_organizador.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_organizador = (int) $_SESSION["organizador_id"];
$id_organizacao = (int) $_SESSION["organizador_id_organizacao"];

$sql = "SELECT 
            C.id_comentario, 
            C.comentario, 
            E.nome AS evento_nome, 
            E.data AS evento_data, 
            U.nome AS aluno_nome
        FROM Comentario C
        INNER JOIN Evento E ON E.id_evento = C.id_evento
        INNER JOIN Aluno A ON A.id_aluno = C.id_aluno
        INNER JOIN Usuario U ON U.id_usuario = A.id_aluno
        WHERE E.id_organizacao = ?
        ORDER BY E.data DESC, C.id_comentario DESC";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_organizacao);
$stmt->execute();
$resultado = $stmt->get_result();

$data = [];
while ($row = $resultado->fetch_assoc()) {
    // formatar data
    $timestamp = strtotime($row["evento_data"]);
    $row["evento_data_formatada"] = date("d/m/Y H:i", $timestamp);
    $data[] = $row;
}
$stmt->close();
$conexao->close();

$retorno = [
    "status" => "ok",
    "mensagem" => "Feedbacks carregados.",
    "data" => $data,
];

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
