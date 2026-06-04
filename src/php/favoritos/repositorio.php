<?php

function favorito_local_existe(mysqli $conexao, int $id_local)
{
    $stmt = $conexao->prepare("SELECT id_local FROM Locais WHERE id_local = ? LIMIT 1");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $id_local);

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

function favorito_existe(mysqli $conexao, int $id_aluno, int $id_local)
{
    $stmt = $conexao->prepare("SELECT id_favorito FROM Favorito WHERE id_aluno = ? AND id_local = ? LIMIT 1");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("ii", $id_aluno, $id_local);

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

function favorito_inserir(mysqli $conexao, int $id_aluno, int $id_local): int
{
    $stmt = $conexao->prepare("INSERT INTO Favorito (id_aluno, id_local) VALUES (?, ?)");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("ii", $id_aluno, $id_local);
    $stmt->execute();

    $id_favorito = $stmt->affected_rows > 0 ? (int) $conexao->insert_id : 0;
    $stmt->close();

    return $id_favorito;
}

function favorito_remover(mysqli $conexao, int $id_aluno, int $id_local): bool
{
    $stmt = $conexao->prepare("DELETE FROM Favorito WHERE id_aluno = ? AND id_local = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $id_aluno, $id_local);
    $stmt->execute();
    $removido = $stmt->affected_rows > 0;
    $stmt->close();

    return $removido;
}

function favorito_listar_por_aluno(mysqli $conexao, int $id_aluno)
{
    $sql = "SELECT F.id_favorito, L.id_local, L.id_instituicao, L.tipo_escola, L.tipo,
                L.nome, L.capacidade, L.longitude, L.latitude,
                I.nome AS nome_instituicao
            FROM Favorito F
            INNER JOIN Locais L ON F.id_local = L.id_local
            LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
            WHERE F.id_aluno = ?
            ORDER BY L.nome";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("i", $id_aluno);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $favoritos = [];

    while ($row = $resultado->fetch_assoc()) {
        $favoritos[] = $row;
    }

    $stmt->close();
    return $favoritos;
}
