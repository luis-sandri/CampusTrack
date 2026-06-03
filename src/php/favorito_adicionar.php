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
    $stmt = $conexao->prepare("SELECT id_local FROM Locais WHERE id_local = ?");
    $stmt->bind_param("i", $id_local);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $retorno = [
            "status"   => "not ok",
            "mensagem" => "Local nao encontrado.",
            "data"     => [],
        ];
    } else {
        $stmt->close();

        $stmt = $conexao->prepare(
            "INSERT IGNORE INTO Favorito (id_aluno, id_local) VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $id_aluno, $id_local);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $id_favorito = (int) $conexao->insert_id;
            $retorno = [
                "status"   => "ok",
                "mensagem" => "Local adicionado aos favoritos.",
                "data"     => [["id_favorito" => $id_favorito]],
            ];
        } else {
            $retorno = [
                "status"   => "ok",
                "mensagem" => "Local ja esta nos favoritos.",
                "data"     => [],
            ];
        }

        $stmt->close();
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
