<?php
include_once __DIR__ . "/valida_sessao_organizacao.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_organizacao = (int) $_SESSION["organizacao_id"];

if (isset($_GET["id"])) {
    $id_raw = trim((string) $_GET["id"]);
    $id = ctype_digit($id_raw) ? (int) $id_raw : 0;
    $nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
    $email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";

    if ($id <= 0 || $nome === "" || $email === "") {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Dados invalidos.",
            "data" => [],
        ];
    } else {
        $stmt = $conexao->prepare("SELECT id_organizador FROM Organizador WHERE id_usuario = ? AND id_organizacao = ?");
        $stmt->bind_param("ii", $id, $id_organizacao);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows !== 1) {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Organizador nao encontrado.",
                "data" => [],
            ];
            $stmt->close();
        } else {
            $stmt->close();

            $stmt = $conexao->prepare("SELECT id_usuario FROM Usuario WHERE email = ? AND id_usuario <> ?");
            $stmt->bind_param("si", $email, $id);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {
                $retorno = [
                    "status" => "not ok",
                    "mensagem" => "Ja existe um usuario cadastrado com este e-mail.",
                    "data" => [],
                ];
                $stmt->close();
            } else {
                $stmt->close();

                $stmt = $conexao->prepare("UPDATE Usuario SET nome = ?, email = ? WHERE id_usuario = ?");
                $stmt->bind_param("ssi", $nome, $email, $id);
                $stmt->execute();
                $stmt->close();

                $retorno = [
                    "status" => "ok",
                    "mensagem" => "Organizador alterado com sucesso.",
                    "data" => [],
                ];
            }
        }
    }
} else {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Nao foi possivel alterar o registro sem ID.",
        "data" => [],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
