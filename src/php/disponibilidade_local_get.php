<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/valida_sessao_gerente.php";
include_once __DIR__ . "/evento_funcoes.php";
include_once __DIR__ . "/evento_listagem_funcoes.php";

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
            $data_fim = date("Y-m-d H:i:s", strtotime($data_informada . " +120 minutes"));

            $sql = "SELECT E.id_evento, E.nome AS nome_evento, E.data,
                        L.id_local, L.nome AS nome_local, L.tipo, L.capacidade,
                        'Ocupado' AS status_disponibilidade
                    FROM Evento E
                    INNER JOIN Locais L ON L.id_local = E.id_local
                    WHERE E.status = 'ativo'
                      AND L.id_instituicao = ?
                      AND E.data >= ?
                      AND E.data < ?
                    ORDER BY E.data ASC, L.nome ASC";

            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("iss", $id_instituicao, $data_informada, $data_fim);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $data = [];
            while ($row = $resultado->fetch_assoc()) {
                $row["data_formatada"] = evento_formatar_data_exibicao((string) $row["data"]);
                $data[] = $row;
            }
            $stmt->close();

            $retorno = [
                "status" => "ok",
                "mensagem" => count($data) > 0 ? "Lista carregada." : "Nenhum espaço disponível para o período selecionado",
                "data" => $data,
            ];
        }
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
