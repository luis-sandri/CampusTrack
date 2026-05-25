<?php
include_once __DIR__ . "/valida_sessao_aluno.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status"   => "",
    "mensagem" => "",
    "data"     => [],
];

$id_local_raw = isset($_POST["id_local"]) ? trim((string) $_POST["id_local"]) : "";
$id_local     = ctype_digit($id_local_raw) ? (int) $id_local_raw : 0;
$id_aluno     = (int) $_SESSION["aluno_id"];

if ($id_local <= 0) {
    $retorno = [
        "status"   => "not ok",
        "mensagem" => "Local inválido.",
        "data"     => [],
    ];
} else {
    $stmt = $conexao->prepare(
        "DELETE FROM Favorito WHERE id_aluno = ? AND id_local = ?"
    );
    $stmt->bind_param("ii", $id_aluno, $id_local);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $retorno = [
            "status"   => "ok",
            "mensagem" => "Local removido dos favoritos.",
            "data"     => [],
        ];
    } else {
        $retorno = [
            "status"   => "not ok",
            "mensagem" => "Favorito não encontrado.",
            "data"     => [],
        ];
    }

    $stmt->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
