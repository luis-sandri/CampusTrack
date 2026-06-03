<?php

function local_inserir(mysqli $conexao, array $local): int
{
    $stmt = $conexao->prepare(
        "INSERT INTO Locais (id_instituicao, tipo_escola, tipo, nome, capacidade, longitude, latitude)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "isssiss",
        $local["id_instituicao"],
        $local["tipo_escola"],
        $local["tipo"],
        $local["nome"],
        $local["capacidade"],
        $local["longitude"],
        $local["latitude"]
    );
    $stmt->execute();

    $id = $stmt->affected_rows > 0 ? (int) $conexao->insert_id : 0;
    $stmt->close();

    return $id;
}

function local_existe_na_instituicao(mysqli $conexao, int $id_local, int $id_instituicao): bool
{
    $stmt = $conexao->prepare("SELECT id_local FROM Locais WHERE id_local = ? AND id_instituicao = ? LIMIT 1");
    $stmt->bind_param("ii", $id_local, $id_instituicao);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $existe = $resultado->num_rows === 1;
    $stmt->close();

    return $existe;
}

function local_atualizar(mysqli $conexao, int $id_local, int $id_instituicao_gerente, array $local): bool
{
    $stmt = $conexao->prepare(
        "UPDATE Locais SET id_instituicao = ?, tipo_escola = ?, tipo = ?, nome = ?, capacidade = ?, longitude = ?, latitude = ?
         WHERE id_local = ? AND id_instituicao = ?"
    );
    $stmt->bind_param(
        "isssissii",
        $local["id_instituicao"],
        $local["tipo_escola"],
        $local["tipo"],
        $local["nome"],
        $local["capacidade"],
        $local["longitude"],
        $local["latitude"],
        $id_local,
        $id_instituicao_gerente
    );
    $sucesso = $stmt->execute();
    $stmt->close();

    return $sucesso;
}

function local_excluir(mysqli $conexao, int $id_local, int $id_instituicao): bool
{
    $stmt = $conexao->prepare("DELETE FROM Locais WHERE id_local = ? AND id_instituicao = ?");
    $stmt->bind_param("ii", $id_local, $id_instituicao);
    $stmt->execute();

    $excluido = $stmt->affected_rows > 0;
    $stmt->close();

    return $excluido;
}

function local_buscar_por_id(mysqli $conexao, int $id_local)
{
    $sql = "SELECT L.id_local, L.id_instituicao, L.tipo_escola, L.tipo, L.nome, L.capacidade, L.longitude, L.latitude,
            I.nome AS nome_instituicao
            FROM Locais L
            LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
            WHERE L.id_local = ?";
    $stmt = $conexao->prepare($sql);
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

    $locais = [];

    while ($row = $resultado->fetch_assoc()) {
        $locais[] = $row;
    }

    $stmt->close();
    return $locais;
}

function local_listar_por_instituicao(mysqli $conexao, int $id_instituicao, string $ordem = "L.nome")
{
    $ordens_permitidas = ["L.nome", "L.id_local"];
    if (!in_array($ordem, $ordens_permitidas, true)) {
        $ordem = "L.nome";
    }

    $sql = "SELECT L.id_local, L.id_instituicao, L.tipo_escola, L.tipo, L.nome, L.capacidade, L.longitude, L.latitude,
            I.nome AS nome_instituicao
            FROM Locais L
            LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
            WHERE L.id_instituicao = ?
            ORDER BY " . $ordem;
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_instituicao);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $locais = [];

    while ($row = $resultado->fetch_assoc()) {
        $locais[] = $row;
    }

    $stmt->close();
    return $locais;
}

function local_listar_todos(mysqli $conexao)
{
    $sql = "SELECT L.id_local, L.id_instituicao, L.tipo_escola, L.tipo, L.nome, L.capacidade, L.longitude, L.latitude,
            I.nome AS nome_instituicao
            FROM Locais L
            LEFT JOIN Instituicao I ON L.id_instituicao = I.id_instituicao
            ORDER BY L.id_local";
    $resultado = $conexao->query($sql);

    if (!$resultado) {
        return null;
    }

    $locais = [];

    while ($row = $resultado->fetch_assoc()) {
        $locais[] = $row;
    }

    return $locais;
}
