<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/valida_sessao_aluno.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_aluno = (int) $_SESSION["aluno_id"];
$id_instituicao = (int) $_SESSION["aluno_id_instituicao"];

// Buscar eventos encerrados da instituição que o aluno não comentou
$sql = "SELECT E.id_evento, E.nome, E.data, E.status,
            L.nome AS nome_local,
            O.nome AS nome_organizacao
        FROM Evento E
        INNER JOIN Locais L ON L.id_local = E.id_local
        INNER JOIN Organizacao O ON O.id_organizacao = E.id_organizacao
        LEFT JOIN Comentario C ON C.id_evento = E.id_evento AND C.id_aluno = ?
        WHERE E.status = 'encerrado' 
          AND L.id_instituicao = ?
          AND C.id_comentario IS NULL
        ORDER BY E.data DESC, E.nome ASC";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("ii", $id_aluno, $id_instituicao);
$stmt->execute();
$resultado = $stmt->get_result();

$data = [];
while ($row = $resultado->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();

$retorno = [
    "status" => "ok",
    "mensagem" => "Lista carregada.",
    "data" => $data,
];

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
