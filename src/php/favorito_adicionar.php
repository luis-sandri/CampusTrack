<?php
include_once __DIR__ . "/valida_sessao_aluno.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_aluno = isset($_SESSION["aluno_id"]) ? (int) $_SESSION["aluno_id"] : 0;
$id_local_raw = isset($_POST["id_local"]) ? trim((string) $_POST["id_local"]) : "";
$id_local = ctype_digit($id_local_raw) ? (int) $id_local_raw : 0;

if ($id_aluno <= 0 || $id_local <= 0) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Aluno ou local invalido.",
        "data" => [],
    ];
} else {
    $stmt = $conexao->prepare("SELECT id_favorito FROM Favorito WHERE id_aluno = ? AND id_local = ?");
    $stmt->bind_param("ii", $id_aluno, $id_local);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $stmt->close();
        $stmt = $conexao->prepare("DELETE FROM Favorito WHERE id_aluno = ? AND id_local = ?");
        $stmt->bind_param("ii", $id_aluno, $id_local);
        $stmt->execute();

        $retorno = [
            "status" => "ok",
            "mensagem" => "Favorito removido.",
            "data" => [["favorito" => false]],
        ];
        $stmt->close();
    } else {
        $stmt->close();
        $stmt = $conexao->prepare("INSERT INTO Favorito (id_aluno, id_local) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_aluno, $id_local);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $retorno = [
                "status" => "ok",
                "mensagem" => "Favorito adicionado.",
                "data" => [["favorito" => true]],
            ];
        } else {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Nao foi possivel adicionar o favorito.",
                "data" => [],
            ];
        }
        $stmt->close();
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
