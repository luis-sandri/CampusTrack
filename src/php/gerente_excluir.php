<?php
include_once __DIR__ . "/valida_sessao_admin.php";
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
        $stmt = $conexao->prepare("SELECT id_gerente FROM Gerente_Locais WHERE id_gerente = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows !== 1) {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Gerente nao encontrado.",
                "data" => [],
            ];
            $stmt->close();
        } else {
            $stmt->close();

            $stmt = $conexao->prepare("DELETE FROM Usuario WHERE id_usuario = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $retorno = [
                    "status" => "ok",
                    "mensagem" => "Gerente excluido com sucesso.",
                    "data" => [],
                ];
            } else {
                $retorno = [
                    "status" => "not ok",
                    "mensagem" => "Nao foi possivel excluir o gerente.",
                    "data" => [],
                ];
            }

            $stmt->close();
        }
    }
} else {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Nao foi possivel excluir sem ID.",
        "data" => [],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
