<?php

function autenticacao_buscar_admin_por_email(mysqli $conexao, string $email)
{
    return autenticacao_buscar_usuario_por_email($conexao, "
        SELECT u.id_usuario, u.nome, u.email, u.senha
        FROM Usuario u
        INNER JOIN Administrador a ON a.id_adm = u.id_usuario
        WHERE u.email = ?
        LIMIT 1
    ", $email);
}

function autenticacao_buscar_gerente_por_email(mysqli $conexao, string $email)
{
    return autenticacao_buscar_usuario_por_email($conexao, "
        SELECT u.id_usuario, u.nome, u.email, u.senha, g.id_instituicao
        FROM Usuario u
        INNER JOIN Gerente_Locais g ON g.id_gerente = u.id_usuario
        WHERE u.email = ?
        LIMIT 1
    ", $email);
}

function autenticacao_buscar_organizador_por_email(mysqli $conexao, string $email)
{
    return autenticacao_buscar_usuario_por_email($conexao, "
        SELECT o.id_organizador, o.id_organizacao, u.id_usuario, u.nome, u.email, u.senha
        FROM Organizador o
        INNER JOIN Usuario u ON u.id_usuario = o.id_usuario
        WHERE u.email = ?
        LIMIT 1
    ", $email);
}

function autenticacao_buscar_usuario_por_email(mysqli $conexao, string $sql, string $email)
{
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $usuario = $resultado->num_rows === 0 ? [] : $resultado->fetch_assoc();
    $stmt->close();

    return $usuario;
}
