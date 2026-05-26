<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/valida_sessao_gerente.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$data_input = isset($_GET["data"]) ? trim((string) $_GET["data"]) : "";

if (empty($data_input)) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Data não informada.",
        "data" => [],
    ];
} else {
    $gerente_id = (int) $_SESSION["gerente_id"];

    $sql_gerente = "SELECT escola FROM Gerente_Locais WHERE id_gerente = ?";
    $stmt_gerente = $conexao->prepare($sql_gerente);
    $stmt_gerente->bind_param("i", $gerente_id);
    $stmt_gerente->execute();
    $res_gerente = $stmt_gerente->get_result();

    if ($res_gerente->num_rows > 0) {
        $gerente = $res_gerente->fetch_assoc();
        $escola = $gerente["escola"];
        $id_instituicao = (int) $_SESSION["gerente_id_instituicao"];

        $sql_locais = "
            SELECT L.id_local, L.id_instituicao, L.tipo_escola, L.tipo, L.nome, L.capacidade,
                IF(E.id_evento IS NOT NULL, 'ocupado', 'disponivel') AS status_disponibilidade
            FROM Locais L
            LEFT JOIN Evento E ON L.id_local = E.id_local 
                AND DATE(E.data) = DATE(?) 
                AND E.status = 'ativo'
            WHERE L.id_instituicao = ? 
              AND L.tipo_escola = ?
              AND L.tipo = 'Bloco'
            ORDER BY L.nome
        ";

        $stmt_locais = $conexao->prepare($sql_locais);
        $stmt_locais->bind_param("sis", $data_input, $id_instituicao, $escola);
        $stmt_locais->execute();
        $resultado_locais = $stmt_locais->get_result();

        $data = [];
        while ($row = $resultado_locais->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt_locais->close();

        $retorno = [
            "status" => "ok",
            "mensagem" => "Lista carregada.",
            "data" => $data,
        ];
    } else {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Gerente não possui escola/bloco associado.",
            "data" => [],
        ];
    }
    $stmt_gerente->close();
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
