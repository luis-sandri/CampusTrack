<?php

function recuperacao_aluno_id_por_email(mysqli $conexao, string $email)
{
    return recuperacao_buscar_id($conexao, "
        SELECT u.id_usuario AS id
        FROM Usuario u
        INNER JOIN Aluno a ON u.id_usuario = a.id_aluno
        WHERE u.email = ?
        LIMIT 1
    ", $email);
}

function recuperacao_usuario_id_por_email(mysqli $conexao, string $email)
{
    return recuperacao_buscar_id($conexao, "
        SELECT u.id_usuario AS id
        FROM Usuario u
        LEFT JOIN Gerente_Locais g ON u.id_usuario = g.id_gerente
        LEFT JOIN Organizador o ON u.id_usuario = o.id_usuario
        WHERE u.email = ? AND (g.id_gerente IS NOT NULL OR o.id_usuario IS NOT NULL)
        LIMIT 1
    ", $email);
}

function recuperacao_organizacao_id_por_cnpj(mysqli $conexao, string $cnpj)
{
    return recuperacao_buscar_id($conexao, "
        SELECT id_organizacao AS id
        FROM Organizacao
        WHERE cnpj = ?
        LIMIT 1
    ", $cnpj);
}

function recuperacao_atualizar_senha_usuario(mysqli $conexao, int $id_usuario, string $senha_hash): bool
{
    $stmt = $conexao->prepare("UPDATE Usuario SET senha = ? WHERE id_usuario = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $senha_hash, $id_usuario);
    $sucesso = $stmt->execute();
    $stmt->close();

    return $sucesso;
}

function recuperacao_atualizar_senha_organizacao(mysqli $conexao, int $id_organizacao, string $senha_hash): bool
{
    $stmt = $conexao->prepare("UPDATE Organizacao SET senha = ? WHERE id_organizacao = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("si", $senha_hash, $id_organizacao);
    $sucesso = $stmt->execute();
    $stmt->close();

    return $sucesso;
}

function recuperacao_buscar_id(mysqli $conexao, string $sql, string $valor)
{
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $valor);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $id = $resultado->num_rows === 0 ? 0 : (int) $resultado->fetch_assoc()["id"];
    $stmt->close();

    return $id;
}
