<?php
require_once __DIR__ . "/listagem_funcoes.php";

function evento_local_pertence_instituicao(mysqli $conexao, int $id_local, int $id_instituicao)
{
    $stmt = $conexao->prepare("SELECT id_local FROM Locais WHERE id_local = ? AND id_instituicao = ? LIMIT 1");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("ii", $id_local, $id_instituicao);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $existe = $resultado->num_rows === 1;
    $stmt->close();

    return $existe;
}

function evento_tem_conflito_local(mysqli $conexao, int $id_local, string $data_evento)
{
    $sql = "SELECT id_evento
            FROM Evento
            WHERE id_local = ?
              AND status = 'ativo'
              AND ABS(TIMESTAMPDIFF(MINUTE, data, ?)) < 120
            LIMIT 1";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("is", $id_local, $data_evento);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $tem_conflito = $resultado->num_rows > 0;
    $stmt->close();

    return $tem_conflito;
}

function evento_inserir(mysqli $conexao, array $evento, int $id_organizacao, int $id_organizador): int
{
    $sql = "INSERT INTO Evento (nome, data, status, id_local, id_organizacao, id_organizador)
            VALUES (?, ?, 'pendente', ?, ?, ?)";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("ssiii", $evento["nome"], $evento["data"], $evento["id_local"], $id_organizacao, $id_organizador);
    $stmt->execute();

    $id_evento = $stmt->affected_rows > 0 ? (int) $conexao->insert_id : 0;
    $stmt->close();

    return $id_evento;
}

function evento_pendente_pertence_instituicao(mysqli $conexao, int $id_evento, int $id_instituicao)
{
    $sql = "SELECT E.id_evento
            FROM Evento E
            INNER JOIN Locais L ON L.id_local = E.id_local
            WHERE E.id_evento = ?
              AND L.id_instituicao = ?
              AND E.status = 'pendente'
            LIMIT 1";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("ii", $id_evento, $id_instituicao);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $existe = $resultado->num_rows === 1;
    $stmt->close();

    return $existe;
}

function evento_alterar_status(mysqli $conexao, int $id_evento, string $status): bool
{
    $stmt = $conexao->prepare("UPDATE Evento SET status = ? WHERE id_evento = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $status, $id_evento);
    $stmt->execute();
    $alterado = $stmt->affected_rows > 0;
    $stmt->close();

    return $alterado;
}

function evento_excluir_por_organizacao(mysqli $conexao, int $id_evento, int $id_organizacao): bool
{
    $stmt = $conexao->prepare("DELETE FROM Evento WHERE id_evento = ? AND id_organizacao = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $id_evento, $id_organizacao);
    $stmt->execute();
    $excluido = $stmt->affected_rows > 0;
    $stmt->close();

    return $excluido;
}

function evento_encerrar_por_organizacao(mysqli $conexao, int $id_evento, int $id_organizacao): bool
{
    $sql = "UPDATE Evento
            SET status = 'encerrado'
            WHERE id_evento = ?
              AND id_organizacao = ?
              AND status = 'ativo'";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $id_evento, $id_organizacao);
    $stmt->execute();
    $encerrado = $stmt->affected_rows > 0;
    $stmt->close();

    return $encerrado;
}

function evento_listar_publicos(mysqli $conexao, int $id_instituicao = 0)
{
    $sql = "SELECT E.id_evento, E.nome, E.data, E.status,
                L.id_local, L.nome AS nome_local, L.tipo AS tipo_local, L.capacidade,
                I.id_instituicao, I.nome AS nome_instituicao,
                O.nome AS nome_organizacao
            FROM Evento E
            INNER JOIN Locais L ON L.id_local = E.id_local
            INNER JOIN Instituicao I ON I.id_instituicao = L.id_instituicao
            INNER JOIN Organizacao O ON O.id_organizacao = E.id_organizacao
            WHERE E.status IN ('ativo', 'encerrado')";

    if ($id_instituicao > 0) {
        $sql .= " AND I.id_instituicao = ?";
    }

    $sql .= " ORDER BY E.data ASC, E.nome ASC";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    if ($id_instituicao > 0) {
        $stmt->bind_param("i", $id_instituicao);
    }

    return evento_ler_lista($stmt);
}

function evento_listar_pendentes_por_instituicao(mysqli $conexao, int $id_instituicao)
{
    $sql = "SELECT E.id_evento, E.nome, E.data, E.status,
                L.id_local, L.nome AS nome_local, L.tipo AS tipo_local,
                I.id_instituicao, I.nome AS nome_instituicao,
                O.nome AS nome_organizacao
            FROM Evento E
            INNER JOIN Locais L ON L.id_local = E.id_local
            INNER JOIN Instituicao I ON I.id_instituicao = L.id_instituicao
            INNER JOIN Organizacao O ON O.id_organizacao = E.id_organizacao
            WHERE E.status = 'pendente'
              AND I.id_instituicao = ?
            ORDER BY E.data ASC, E.nome ASC";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $id_instituicao);
    return evento_ler_lista($stmt);
}

function evento_listar_por_organizador(mysqli $conexao, int $id_organizador)
{
    $sql = "SELECT E.id_evento, E.nome, E.data, E.status,
                L.id_local, L.nome AS nome_local, L.tipo AS tipo_local,
                I.id_instituicao, I.nome AS nome_instituicao,
                O.nome AS nome_organizacao
            FROM Evento E
            INNER JOIN Locais L ON L.id_local = E.id_local
            INNER JOIN Instituicao I ON I.id_instituicao = L.id_instituicao
            INNER JOIN Organizacao O ON O.id_organizacao = E.id_organizacao
            WHERE E.id_organizador = ?
            ORDER BY E.data DESC, E.nome ASC";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $id_organizador);
    return evento_ler_lista($stmt);
}

function evento_ler_lista(mysqli_stmt $stmt)
{
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
