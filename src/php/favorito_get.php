<?php
include_once __DIR__ . "/valida_sessao_aluno.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status"   => "",
    "mensagem" => "",
    "data"     => [],
];

$id_aluno = (int) $_SESSION["aluno_id"];

// Retorna os locais favoritados pelo aluno com os dados do local e da instituição
$sql = "SELECT F.id_favorito, L.id_local, L.id_instituicao, L.tipo_escola, L.tipo,
            L.nome, L.capacidade, L.longitude, L.latitude,
            I.nome AS nome_instituicao
        FROM Favorito F
        INNER JOIN Locais L ON F.id_local = L.id_local
        LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
        WHERE F.id_aluno = ?
        ORDER BY L.nome";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_aluno);
$stmt->execute();
$resultado = $stmt->get_result();

$data = [];
while ($row = $resultado->fetch_assoc()) {
    $data[] = $row;
}
$stmt->close();

$retorno = [
    "status"   => "ok",
    "mensagem" => "Lista carregada.",
    "data"     => $data,
];

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
