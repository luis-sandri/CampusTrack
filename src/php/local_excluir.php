<?php
include_once __DIR__ . "/valida_sessao_gerente.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

if (isset($_GET["id"])) {
    $id_raw = trim((string) $_GET["id"]);
    $id = ctype_digit($id_raw) ? (int) $id_raw : 0;

    if ($id <= 0) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "ID invalido.",
            "data" => [],
        ];
    } else {
        $id_instituicao_gerente = (int) $_SESSION["gerente_id_instituicao"];
        $stmt = $conexao->prepare("DELETE FROM Locais WHERE id_local = ? AND id_instituicao = ?");
        $stmt->bind_param("ii", $id, $id_instituicao_gerente);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $retorno = [
                "status" => "ok",
                "mensagem" => "Registro excluído com sucesso.",
                "data" => [],
            ];
        } else {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Não foi possível excluir o registro.",
                "data" => [],
            ];
        }

        $stmt->close();
    }
} else {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Não foi possível excluir sem ID.",
        "data" => [],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
