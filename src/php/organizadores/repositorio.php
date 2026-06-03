<?php
require_once __DIR__ . "/../usuarios/repositorio.php";

function organizador_existe_na_organizacao(mysqli $conexao, int $id_usuario, int $id_organizacao): bool
{
    $stmt = $conexao->prepare("SELECT id_organizador FROM Organizador WHERE id_usuario = ? AND id_organizacao = ? LIMIT 1");
    $stmt->bind_param("ii", $id_usuario, $id_organizacao);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $existe = $resultado->num_rows === 1;
    $stmt->close();

    return $existe;
}

function organizador_inserir(mysqli $conexao, int $id_organizacao, array $organizador): int
{
    $conexao->begin_transaction();

    try {
        $id_usuario = usuario_inserir($conexao, $organizador);

        if ($id_usuario <= 0) {
            $conexao->rollback();
            return 0;
        }

        $stmt = $conexao->prepare("INSERT INTO Organizador (id_usuario, id_organizacao) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_usuario, $id_organizacao);
        $stmt->execute();
        $inserido = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$inserido) {
            $conexao->rollback();
            return 0;
        }

        $conexao->commit();
        return $id_usuario;
    } catch (Throwable $e) {
        $conexao->rollback();
        return 0;
    }
}

function organizador_atualizar(mysqli $conexao, int $id_usuario, array $organizador): bool
{
    return usuario_atualizar($conexao, $id_usuario, $organizador);
}

function organizador_excluir(mysqli $conexao, int $id_usuario): bool
{
    return usuario_excluir($conexao, $id_usuario);
}

function organizador_buscar_por_id(mysqli $conexao, int $id_usuario, int $id_organizacao)
{
    $sql = "SELECT u.id_usuario, u.nome, u.email, o.id_organizador, o.id_organizacao
            FROM Organizador o
            INNER JOIN Usuario u ON u.id_usuario = o.id_usuario
            WHERE u.id_usuario = ? AND o.id_organizacao = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ii", $id_usuario, $id_organizacao);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $organizadores = [];

    while ($row = $resultado->fetch_assoc()) {
        $organizadores[] = $row;
    }

    $stmt->close();
    return $organizadores;
}

function organizador_listar_por_organizacao(mysqli $conexao, int $id_organizacao)
{
    $sql = "SELECT u.id_usuario, u.nome, u.email, o.id_organizador, o.id_organizacao
            FROM Organizador o
            INNER JOIN Usuario u ON u.id_usuario = o.id_usuario
            WHERE o.id_organizacao = ?
            ORDER BY o.id_organizador";
    $stmt = $conexao->prepare($sql);
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

    $organizadores = [];

    while ($row = $resultado->fetch_assoc()) {
        $organizadores[] = $row;
    }

    $stmt->close();
    return $organizadores;
}
