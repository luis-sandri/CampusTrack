<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/valida_sessao_organizador.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

if (isset($_GET["id_instituicao"]) && isset($_GET["data"])) {
    $id_instituicao_raw = trim((string) $_GET["id_instituicao"]);
    $id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;
    $data_input = trim((string) $_GET["data"]); // Expected format: YYYY-MM-DDTHH:MM or YYYY-MM-DD

    // Basic date validation
    if ($id_instituicao <= 0 || $data_input === "") {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Instituição ou data inválida.",
            "data" => [],
        ];
    } else {
        // Extract just the date part for comparison (blocks the space for the whole day)
        // Or if we want exact datetime, we can use the full string. Let's use DATE() to block the day.
        $sql = "SELECT L.id_local, L.id_instituicao, L.tipo_escola, L.tipo, L.nome, L.capacidade, L.longitude, L.latitude,
                I.nome AS nome_instituicao
                FROM Locais L
                LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
                WHERE L.id_instituicao = ?
                AND L.tipo = 'Bloco'
                AND L.id_local NOT IN (
                    SELECT E.id_local FROM Evento E 
                    WHERE DATE(E.data) = DATE(?) AND E.status = 'ativo'
                )
                ORDER BY L.nome";
                
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("is", $id_instituicao, $data_input);
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
} else {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Parâmetros insuficientes.",
        "data" => [],
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
