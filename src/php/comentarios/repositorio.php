<?php

function comentario_buscar_evento_avaliavel(mysqli $conexao, int $id_evento)
{
    $stmt = $conexao->prepare("SELECT data, status FROM Evento WHERE id_evento = ? AND status IN ('ativo', 'encerrado')");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $id_evento);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $evento = $resultado->num_rows === 0 ? [] : $resultado->fetch_assoc();
    $stmt->close();

    return $evento;
}

function comentario_evento_encerrado(array $evento): bool
{
    if ($evento["status"] === "encerrado") {
        return true;
    }

    $data_fim_timestamp = strtotime($evento["data"]) + (120 * 60);
    return time() > $data_fim_timestamp;
}

function comentario_aluno_ja_enviou(mysqli $conexao, int $id_aluno, int $id_evento)
{
    $stmt = $conexao->prepare("SELECT id_comentario FROM Comentario WHERE id_aluno = ? AND id_evento = ? LIMIT 1");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("ii", $id_aluno, $id_evento);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $existe = $resultado->num_rows > 0;
    $stmt->close();

    return $existe;
}

function comentario_inserir(mysqli $conexao, int $id_aluno, array $comentario): bool
{
    $stmt = $conexao->prepare("INSERT INTO Comentario (id_aluno, id_evento, comentario) VALUES (?, ?, ?)");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("iis", $id_aluno, $comentario["id_evento"], $comentario["comentario"]);
    $stmt->execute();
    $inserido = $stmt->affected_rows > 0;
    $stmt->close();

    return $inserido;
}

function comentario_listar_feedbacks_por_organizacao(mysqli $conexao, int $id_organizacao)
{
    $sql = "SELECT C.id_comentario,
                C.comentario,
                E.nome AS evento_nome,
                E.data AS evento_data,
                U.nome AS aluno_nome
            FROM Comentario C
            INNER JOIN Evento E ON E.id_evento = C.id_evento
            INNER JOIN Aluno A ON A.id_aluno = C.id_aluno
            INNER JOIN Usuario U ON U.id_usuario = A.id_aluno
            WHERE E.id_organizacao = ?
            ORDER BY E.data DESC, C.id_comentario DESC";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $id_organizacao);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $feedbacks = [];

    while ($row = $resultado->fetch_assoc()) {
        $timestamp = strtotime($row["evento_data"]);
        $row["evento_data_formatada"] = date("d/m/Y H:i", $timestamp);
        $feedbacks[] = $row;
    }

    $stmt->close();
    return $feedbacks;
}
