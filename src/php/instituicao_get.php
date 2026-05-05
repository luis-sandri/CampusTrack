<?php
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
        $stmt = $conexao->prepare("SELECT id_instituicao, nome FROM Instituicao WHERE id_instituicao = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $data = [];
        while ($row = $resultado->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();

        if (count($data) > 0) {
            $retorno = [
                "status" => "ok",
                "mensagem" => "Registro encontrado.",
                "data" => $data,
            ];
        } else {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Registro não encontrado.",
                "data" => [],
            ];
        }
    }
} else {
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
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
