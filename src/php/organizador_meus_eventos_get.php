<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/valida_sessao_organizador.php";
include_once __DIR__ . "/evento_listagem_funcoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_organizador = (int) $_SESSION["organizador_id"];

$sql = "SELECT E.id_evento, E.nome, E.data, E.status,
            L.nome AS nome_local
        FROM Evento E
        INNER JOIN Locais L ON L.id_local = E.id_local
        WHERE E.id_organizador = ?
        ORDER BY E.data DESC, E.nome ASC";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_organizador);
$stmt->execute();
$resultado = $stmt->get_result();

$data = [];
while ($row = $resultado->fetch_assoc()) {
    $row["data_formatada"] = evento_formatar_data_exibicao((string) $row["data"]);
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
