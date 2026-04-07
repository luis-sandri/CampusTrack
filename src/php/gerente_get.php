<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

if (isset($_GET["id"])) {
    $id = (int) $_GET["id"];

    $sql = "SELECT u.id_usuario, u.nome, u.email, u.senha, g.id_instituicao, g.escola,
            i.nome AS nome_instituicao
            FROM Usuario u
            INNER JOIN Gerente_locais g ON g.id_gerente = u.id_usuario
            LEFT JOIN Instituicao i ON g.id_instituicao = i.id_instituicao
            WHERE u.id_usuario = ?";
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
} else {
    $sql = "SELECT u.id_usuario, u.nome, u.email, g.id_instituicao, g.escola,
            i.nome AS nome_instituicao
            FROM Usuario u
            INNER JOIN Gerente_locais g ON g.id_gerente = u.id_usuario
            LEFT JOIN Instituicao i ON g.id_instituicao = i.id_instituicao
            ORDER BY u.id_usuario";
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
            "mensagem" => "Não foi possível carregar os gerentes.",
            "data" => [],
        ];
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
