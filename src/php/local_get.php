<?php
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

if (isset($_GET["id_instituicao"])) {
    $id_instituicao_raw = trim((string) $_GET["id_instituicao"]);
    $id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;

    if ($id_instituicao <= 0) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Instituicao invalida.",
            "data" => [],
        ];
    } else {
        $sql = "SELECT L.id_local, L.id_instituicao, L.tipo_escola, L.tipo, L.nome, L.capacidade, L.longitude, L.latitude,
            I.nome AS nome_instituicao
            FROM Locais L
            LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
            WHERE L.id_instituicao = ?
            ORDER BY L.nome";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("i", $id_instituicao);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $data = [];
        while ($row = $resultado->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();

        $retorno = [
            "status" => "ok",
            "mensagem" => "Lista carregada.",
            "data" => $data,
        ];
    }
} else if (isset($_GET["gerente"])) {
    include_once __DIR__ . "/valida_sessao_gerente.php";

    $id_instituicao = (int) $_SESSION["gerente_id_instituicao"];
    $sql = "SELECT L.id_local, L.id_instituicao, L.tipo_escola, L.tipo, L.nome, L.capacidade, L.longitude, L.latitude,
            I.nome AS nome_instituicao
            FROM Locais L
            LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
            WHERE L.id_instituicao = ?
            ORDER BY L.id_local";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_instituicao);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $data = [];
    while ($row = $resultado->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();

    $retorno = [
        "status" => "ok",
        "mensagem" => "Lista carregada.",
        "data" => $data,
    ];
} else if (isset($_GET["id"])) {
    $id_raw = trim((string) $_GET["id"]);
    $id = ctype_digit($id_raw) ? (int) $id_raw : 0;

    if ($id <= 0) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "ID invalido.",
            "data" => [],
        ];
    } else {
        $sql = "SELECT L.id_local, L.id_instituicao, L.tipo_escola, L.tipo, L.nome, L.capacidade, L.longitude, L.latitude,
            I.nome AS nome_instituicao
            FROM Locais L
            LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
            WHERE L.id_local = ?";
        $stmt = $conexao->prepare($sql);
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
    $sql = "SELECT L.id_local, L.id_instituicao, L.tipo_escola, L.tipo, L.nome, L.capacidade, L.longitude, L.latitude,
            I.nome AS nome_instituicao
            FROM Locais L
            LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
            ORDER BY L.id_local";
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
            "mensagem" => "Não foi possível carregar os locais.",
            "data" => [],
        ];
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
