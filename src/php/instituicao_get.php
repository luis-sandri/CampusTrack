<?php
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$sql = "SELECT id_instituicao, nome FROM Instituicao ORDER BY nome";
$result = $conexao->query($sql);

$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $retorno = [
        "status" => "ok",
        "mensagem" => "Lista carregada.",
        "data" => $data,
    ];
} else {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Não foi possível carregar as instituições.",
        "data" => [],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
