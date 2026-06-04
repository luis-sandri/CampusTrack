<?php
require_once __DIR__ . "/../eventos/listagem_funcoes.php";

function disponibilidade_listar_ocupados(mysqli $conexao, int $id_instituicao, string $data_inicio)
{
    $data_fim = date("Y-m-d H:i:s", strtotime($data_inicio . " +120 minutes"));
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

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("iss", $id_instituicao, $data_inicio, $data_fim);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $eventos = [];

    while ($row = $resultado->fetch_assoc()) {
        $row["data_formatada"] = evento_formatar_data_exibicao((string) $row["data"]);
        $eventos[] = $row;
    }

    $stmt->close();
    return $eventos;
}
