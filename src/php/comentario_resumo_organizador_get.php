<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/valida_sessao_organizador.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_organizador = (int) $_SESSION["organizador_id"];

// Get events of the organizer that are 'encerrado' and left outer join with Comentario
$sql = "SELECT E.id_evento, E.nome AS nome_evento, E.data,
            C.id_comentario, C.comentario, A.curso
        FROM Evento E
        LEFT JOIN Comentario C ON C.id_evento = E.id_evento
        LEFT JOIN Aluno A ON A.id_aluno = C.id_aluno
        WHERE E.id_organizador = ? AND E.status = 'encerrado'
        ORDER BY E.data DESC, C.id_comentario DESC";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_organizador);
$stmt->execute();
$resultado = $stmt->get_result();

$eventos = [];
while ($row = $resultado->fetch_assoc()) {
    $id_evento = $row["id_evento"];
    if (!isset($eventos[$id_evento])) {
        $eventos[$id_evento] = [
            "id_evento" => $id_evento,
            "nome_evento" => $row["nome_evento"],
            "data" => $row["data"],
            "total_comentarios" => 0,
            "comentarios" => []
        ];
    }
    
    if ($row["id_comentario"]) {
        $eventos[$id_evento]["total_comentarios"]++;
        $eventos[$id_evento]["comentarios"][] = [
            "curso" => $row["curso"],
            "texto" => $row["comentario"]
        ];
    }
}
$stmt->close();

$data = array_values($eventos);

$retorno = [
    "status" => "ok",
    "mensagem" => "Lista carregada.",
    "data" => $data,
];

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
