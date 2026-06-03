<?php

function instituicao_inserir(mysqli $conexao, array $instituicao): int
{
    $stmt = $conexao->prepare("INSERT INTO Instituicao (nome) VALUES (?)");
    $stmt->bind_param("s", $instituicao["nome"]);
    $stmt->execute();

    $id = $stmt->affected_rows > 0 ? (int) $conexao->insert_id : 0;
    $stmt->close();

    return $id;
}

function instituicao_existe(mysqli $conexao, int $id_instituicao): bool
{
    $stmt = $conexao->prepare("SELECT id_instituicao FROM Instituicao WHERE id_instituicao = ? LIMIT 1");
    $stmt->bind_param("i", $id_instituicao);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $existe = $resultado->num_rows === 1;
    $stmt->close();

    return $existe;
}

function instituicao_atualizar(mysqli $conexao, int $id_instituicao, array $instituicao): bool
{
    $stmt = $conexao->prepare("UPDATE Instituicao SET nome = ? WHERE id_instituicao = ?");
    $stmt->bind_param("si", $instituicao["nome"], $id_instituicao);
    $sucesso = $stmt->execute();
    $stmt->close();

    return $sucesso;
}

function instituicao_excluir(mysqli $conexao, int $id_instituicao): bool
{
    $stmt = $conexao->prepare("DELETE FROM Instituicao WHERE id_instituicao = ?");
    $stmt->bind_param("i", $id_instituicao);
    $stmt->execute();

    $excluido = $stmt->affected_rows > 0;
    $stmt->close();

    return $excluido;
}

function instituicao_buscar_por_id(mysqli $conexao, int $id_instituicao)
{
    $stmt = $conexao->prepare("SELECT id_instituicao, nome FROM Instituicao WHERE id_instituicao = ?");
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

    $instituicoes = [];

    while ($row = $resultado->fetch_assoc()) {
        $instituicoes[] = $row;
    }

    $stmt->close();
    return $instituicoes;
}

function instituicao_listar(mysqli $conexao)
{
    $resultado = $conexao->query("SELECT id_instituicao, nome FROM Instituicao ORDER BY nome");

    if (!$resultado) {
        return null;
    }

    $instituicoes = [];

    while ($row = $resultado->fetch_assoc()) {
        $instituicoes[] = $row;
    }

    return $instituicoes;
}
