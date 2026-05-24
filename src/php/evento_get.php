<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/evento_listagem_funcoes.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_instituicao = evento_obter_id_instituicao_filtro($_GET, $_SESSION);

if ((isset($_GET["id"]) || isset($_GET["id_instituicao"])) && $id_instituicao <= 0) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Instituicao invalida.",
        "data" => [],
    ];
} else {
    $sql = "SELECT E.id_evento, E.nome, E.data, E.status,
                L.id_local, L.nome AS nome_local, L.tipo AS tipo_local, L.capacidade,
                I.id_instituicao, I.nome AS nome_instituicao,
                O.nome AS nome_organizacao
            FROM Evento E
            INNER JOIN Locais L ON L.id_local = E.id_local
            INNER JOIN Instituicao I ON I.id_instituicao = L.id_instituicao
            INNER JOIN Organizacao O ON O.id_organizacao = E.id_organizacao
            WHERE E.status = 'ativo'";

    if ($id_instituicao > 0) {
        $sql .= " AND I.id_instituicao = ?";
    }

    $sql .= " ORDER BY E.data ASC, E.nome ASC";
    $stmt = $conexao->prepare($sql);

    if ($id_instituicao > 0) {
        $stmt->bind_param("i", $id_instituicao);
    }

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
        "mensagem" => "Lista carregada.",
        "data" => $data,
    ];
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
