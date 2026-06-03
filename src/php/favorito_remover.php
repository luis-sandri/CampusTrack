<?php
include_once __DIR__ . "/valida_sessao_aluno.php";
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status"   => "",
    "mensagem" => "",
    "data"     => [],
];

$erros = [];
$id_local = campo_inteiro_positivo_obrigatorio($_POST, "id_local", "Local", $erros);
$id_aluno = (int) $_SESSION["aluno_id"];

if (count($erros) > 0) {
    $retorno = retorno_validacao($erros);
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
            "mensagem" => "Favorito nao encontrado.",
            "data"     => [],
        ];
    }

    $stmt->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
