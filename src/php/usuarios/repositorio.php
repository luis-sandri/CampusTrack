<?php
require_once __DIR__ . "/../core/validacoes.php";

function usuario_email_em_uso(mysqli $conexao, string $email, int $ignorar_id = 0): bool
{
    if ($ignorar_id > 0) {
        $stmt = $conexao->prepare("SELECT id_usuario FROM Usuario WHERE email = ? AND id_usuario <> ? LIMIT 1");
        $stmt->bind_param("si", $email, $ignorar_id);
    } else {
        $stmt = $conexao->prepare("SELECT id_usuario FROM Usuario WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $em_uso = $resultado->num_rows > 0;
    $stmt->close();

    return $em_uso;
}

function usuario_inserir(mysqli $conexao, array $usuario): int
{
    $senha_hash = senha_hash($usuario["senha"]);
    $stmt = $conexao->prepare("INSERT INTO Usuario (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $usuario["nome"], $usuario["email"], $senha_hash);
    $stmt->execute();

    $id_usuario = $stmt->affected_rows > 0 ? (int) $conexao->insert_id : 0;
    $stmt->close();

    return $id_usuario;
}

function usuario_atualizar(mysqli $conexao, int $id_usuario, array $usuario): bool
{
    $stmt = $conexao->prepare("UPDATE Usuario SET nome = ?, email = ? WHERE id_usuario = ?");
    $stmt->bind_param("ssi", $usuario["nome"], $usuario["email"], $id_usuario);
    $sucesso = $stmt->execute();
    $stmt->close();

    return $sucesso;
}

function usuario_excluir(mysqli $conexao, int $id_usuario): bool
{
    $stmt = $conexao->prepare("DELETE FROM Usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $excluido = $stmt->affected_rows > 0;
    $stmt->close();

    return $excluido;
}
