<?php

function organizacao_cnpj_em_uso(mysqli $conexao, string $cnpj)
{
    $stmt = $conexao->prepare("SELECT id_organizacao FROM Organizacao WHERE cnpj = ? LIMIT 1");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $cnpj);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $em_uso = $resultado->num_rows > 0;
    $stmt->close();

    return $em_uso;
}

function organizacao_inserir(mysqli $conexao, array $organizacao): int
{
    $senha_hash = senha_hash($organizacao["senha"]);
    $stmt = $conexao->prepare("INSERT INTO Organizacao (nome, cnpj, senha) VALUES (?, ?, ?)");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("sss", $organizacao["nome"], $organizacao["cnpj"], $senha_hash);
    $stmt->execute();

    $id_organizacao = $stmt->affected_rows > 0 ? (int) $conexao->insert_id : 0;
    $stmt->close();

    return $id_organizacao;
}

function organizacao_buscar_por_cnpj(mysqli $conexao, string $cnpj)
{
    $stmt = $conexao->prepare("SELECT id_organizacao, nome, cnpj, senha FROM Organizacao WHERE cnpj = ? LIMIT 1");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("s", $cnpj);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $organizacao = $resultado->num_rows === 0 ? [] : $resultado->fetch_assoc();
    $stmt->close();

    return $organizacao;
}
