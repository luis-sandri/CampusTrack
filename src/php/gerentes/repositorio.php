<?php
require_once __DIR__ . "/../usuarios/repositorio.php";

function gerente_existe(mysqli $conexao, int $id_gerente): bool
{
    $stmt = $conexao->prepare("SELECT id_gerente FROM Gerente_Locais WHERE id_gerente = ? LIMIT 1");
    $stmt->bind_param("i", $id_gerente);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $existe = $resultado->num_rows === 1;
    $stmt->close();

    return $existe;
}

function gerente_inserir(mysqli $conexao, array $gerente): int
{
    $conexao->begin_transaction();

    try {
        $id_usuario = usuario_inserir($conexao, $gerente);

        if ($id_usuario <= 0) {
            $conexao->rollback();
            return 0;
        }

        $stmt = $conexao->prepare("INSERT INTO Gerente_Locais (id_gerente, id_instituicao, escola) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_usuario, $gerente["id_instituicao"], $gerente["escola"]);
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

function gerente_atualizar(mysqli $conexao, int $id_gerente, array $gerente): bool
{
    $conexao->begin_transaction();

    try {
        if (!usuario_atualizar($conexao, $id_gerente, $gerente)) {
            $conexao->rollback();
            return false;
        }

        $stmt = $conexao->prepare("UPDATE Gerente_Locais SET id_instituicao = ?, escola = ? WHERE id_gerente = ?");
        $stmt->bind_param("isi", $gerente["id_instituicao"], $gerente["escola"], $id_gerente);
        $sucesso = $stmt->execute();
        $stmt->close();

        if (!$sucesso) {
            $conexao->rollback();
            return false;
        }

        $conexao->commit();
        return true;
    } catch (Throwable $e) {
        $conexao->rollback();
        return false;
    }
}

function gerente_excluir(mysqli $conexao, int $id_gerente): bool
{
    return usuario_excluir($conexao, $id_gerente);
}

function gerente_buscar_por_id(mysqli $conexao, int $id_gerente)
{
    $sql = "SELECT u.id_usuario, u.nome, u.email, g.id_instituicao, g.escola,
            i.nome AS nome_instituicao
            FROM Usuario u
            INNER JOIN Gerente_Locais g ON g.id_gerente = u.id_usuario
            LEFT JOIN Instituicao i ON g.id_instituicao = i.id_instituicao
            WHERE u.id_usuario = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id_gerente);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $gerentes = [];

    while ($row = $resultado->fetch_assoc()) {
        $gerentes[] = $row;
    }

    $stmt->close();
    return $gerentes;
}

function gerente_listar(mysqli $conexao)
{
    $sql = "SELECT u.id_usuario, u.nome, u.email, g.id_instituicao, g.escola,
            i.nome AS nome_instituicao
            FROM Usuario u
            INNER JOIN Gerente_Locais g ON g.id_gerente = u.id_usuario
            LEFT JOIN Instituicao i ON g.id_instituicao = i.id_instituicao
            ORDER BY u.id_usuario";
    $resultado = $conexao->query($sql);

    if (!$resultado) {
        return null;
    }

    $gerentes = [];

    while ($row = $resultado->fetch_assoc()) {
        $gerentes[] = $row;
    }

    return $gerentes;
}
