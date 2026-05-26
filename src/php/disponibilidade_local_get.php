<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/valida_sessao_gerente.php";
include_once __DIR__ . "/evento_funcoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

if (!isset($_GET["data"])) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Data não informada.",
        "data" => [],
    ];
} else {
    $data_informada = evento_normalizar_data_hora($_GET["data"]);

    if ($data_informada === "") {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Data ou horário inválidos. Informe um período futuro para realizar a consulta.",
            "data" => [],
        ];
    } else {
        $data_timestamp = strtotime($data_informada);
        $data_atual_timestamp = time();

        if ($data_timestamp <= $data_atual_timestamp) {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Data ou horário inválidos. Informe um período futuro para realizar a consulta.",
                "data" => [],
            ];
        } else {
            $id_instituicao = (int) $_SESSION["gerente_id_instituicao"];

            $sql = "SELECT L.id_local, L.nome, L.tipo, L.capacidade,
                        CASE WHEN E.id_evento IS NOT NULL THEN 'Ocupado' ELSE 'Disponível' END AS status_disponibilidade
                    FROM Locais L
                    LEFT JOIN (
                        SELECT DISTINCT id_local, 1 as id_evento
                        FROM Evento
                        WHERE status = 'ativo' 
                          AND data <= ? AND DATE_ADD(data, INTERVAL 120 MINUTE) > ?
                    ) E ON L.id_local = E.id_local
                    WHERE L.id_instituicao = ?
                    ORDER BY L.nome";

            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("ssi", $data_informada, $data_informada, $id_instituicao);
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
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
