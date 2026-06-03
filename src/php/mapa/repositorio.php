<?php

function mapa_no_inserir(mysqli $conexao, array $no): int
{
    $stmt = $conexao->prepare("INSERT INTO Mapa_No (id_instituicao, nome, longitude, latitude) VALUES (?, ?, ?, ?)");

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("isss", $no["id_instituicao"], $no["nome"], $no["longitude"], $no["latitude"]);
    $stmt->execute();

    $id_no = $stmt->affected_rows > 0 ? (int) $conexao->insert_id : 0;
    $stmt->close();

    return $id_no;
}

function mapa_no_atualizar(mysqli $conexao, int $id_no, array $no): bool
{
    $stmt = $conexao->prepare("UPDATE Mapa_No SET nome = ?, longitude = ?, latitude = ? WHERE id_no = ? AND id_instituicao = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("sssii", $no["nome"], $no["longitude"], $no["latitude"], $id_no, $no["id_instituicao"]);
    $alterado = $stmt->execute();
    $stmt->close();

    return $alterado;
}

function mapa_no_pertence_instituicao(mysqli $conexao, int $id_no, int $id_instituicao)
{
    $stmt = $conexao->prepare("SELECT id_no FROM Mapa_No WHERE id_no = ? AND id_instituicao = ? LIMIT 1");

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("ii", $id_no, $id_instituicao);

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

function mapa_no_excluir(mysqli $conexao, int $id_no, int $id_instituicao): bool
{
    $stmt = $conexao->prepare("DELETE FROM Mapa_No WHERE id_no = ? AND id_instituicao = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $id_no, $id_instituicao);
    $stmt->execute();
    $excluido = $stmt->affected_rows > 0;
    $stmt->close();

    return $excluido;
}

function mapa_buscar_nos_por_ids(mysqli $conexao, int $id_instituicao, int $id_no_origem, int $id_no_destino)
{
    $sql = "SELECT id_no, longitude, latitude
            FROM Mapa_No
            WHERE id_instituicao = ?
              AND id_no IN (?, ?)";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param("iii", $id_instituicao, $id_no_origem, $id_no_destino);

    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $resultado = $stmt->get_result();

    if (!$resultado) {
        $stmt->close();
        return null;
    }

    $nos = [];

    while ($row = $resultado->fetch_assoc()) {
        $nos[(int) $row["id_no"]] = $row;
    }

    $stmt->close();
    return $nos;
}

function mapa_aresta_existe_conexao(mysqli $conexao, array $aresta)
{
    $sql = "SELECT id_aresta
            FROM Mapa_Aresta
            WHERE id_instituicao = ?
              AND ((id_no_origem = ? AND id_no_destino = ?) OR (id_no_origem = ? AND id_no_destino = ?))";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param(
        "iiiii",
        $aresta["id_instituicao"],
        $aresta["id_no_origem"],
        $aresta["id_no_destino"],
        $aresta["id_no_destino"],
        $aresta["id_no_origem"]
    );

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

function mapa_aresta_inserir(mysqli $conexao, array $aresta, float $distancia): int
{
    $sql = "INSERT INTO Mapa_Aresta (id_instituicao, id_no_origem, id_no_destino, distancia_metros)
            VALUES (?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("iiid", $aresta["id_instituicao"], $aresta["id_no_origem"], $aresta["id_no_destino"], $distancia);
    $stmt->execute();

    $id_aresta = $stmt->affected_rows > 0 ? (int) $conexao->insert_id : 0;
    $stmt->close();

    return $id_aresta;
}

function mapa_aresta_excluir(mysqli $conexao, int $id_aresta, int $id_instituicao): bool
{
    $stmt = $conexao->prepare("DELETE FROM Mapa_Aresta WHERE id_aresta = ? AND id_instituicao = ?");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("ii", $id_aresta, $id_instituicao);
    $stmt->execute();
    $excluido = $stmt->affected_rows > 0;
    $stmt->close();

    return $excluido;
}

function mapa_grafo_buscar(mysqli $conexao, int $id_instituicao)
{
    $nos = mapa_listar_nos($conexao, $id_instituicao);

    if ($nos === null) {
        return null;
    }

    $arestas = mapa_listar_arestas($conexao, $id_instituicao);

    if ($arestas === null) {
        return null;
    }

    return [
        "nos" => $nos,
        "arestas" => $arestas,
    ];
}

function mapa_listar_nos(mysqli $conexao, int $id_instituicao)
{
    $sql = "SELECT id_no, id_instituicao, nome, longitude, latitude
            FROM Mapa_No
            WHERE id_instituicao = ?
            ORDER BY id_no";
    return mapa_listar_por_instituicao($conexao, $sql, $id_instituicao);
}

function mapa_listar_arestas(mysqli $conexao, int $id_instituicao)
{
    $sql = "SELECT id_aresta, id_instituicao, id_no_origem, id_no_destino, distancia_metros
            FROM Mapa_Aresta
            WHERE id_instituicao = ?
            ORDER BY id_aresta";
    return mapa_listar_por_instituicao($conexao, $sql, $id_instituicao);
}

function mapa_listar_por_instituicao(mysqli $conexao, string $sql, int $id_instituicao)
{
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return null;
    }

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

    $itens = [];

    while ($row = $resultado->fetch_assoc()) {
        $itens[] = $row;
    }

    $stmt->close();
    return $itens;
}

function mapa_distancia_metros(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $raio_terra = 6371000;
    $d_lat = deg2rad($lat2 - $lat1);
    $d_lon = deg2rad($lon2 - $lon1);
    $a = sin($d_lat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($d_lon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return round($raio_terra * $c, 2);
}
