<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";

function distancia_metros(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $raio_terra = 6371000;
    $d_lat = deg2rad($lat2 - $lat1);
    $d_lon = deg2rad($lon2 - $lon1);
    $a = sin($d_lat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($d_lon / 2) ** 2;
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($raio_terra * $c, 2);
}

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$id_instituicao_raw = isset($_POST["id_instituicao"]) ? trim((string) $_POST["id_instituicao"]) : "";
$id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;
$id_no_origem_raw = isset($_POST["id_no_origem"]) ? trim((string) $_POST["id_no_origem"]) : "";
$id_no_origem = ctype_digit($id_no_origem_raw) ? (int) $id_no_origem_raw : 0;
$id_no_destino_raw = isset($_POST["id_no_destino"]) ? trim((string) $_POST["id_no_destino"]) : "";
$id_no_destino = ctype_digit($id_no_destino_raw) ? (int) $id_no_destino_raw : 0;

if ($id_instituicao <= 0 || $id_no_origem <= 0 || $id_no_destino <= 0 || $id_no_origem === $id_no_destino) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Instituicao, origem e destino validos sao obrigatorios.",
        "data" => [],
    ];
} else {
    $stmt = $conexao->prepare("
        SELECT id_no, longitude, latitude
        FROM Mapa_No
        WHERE id_instituicao = ? AND id_no IN (?, ?)
    ");
    $stmt->bind_param("iii", $id_instituicao, $id_no_origem, $id_no_destino);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $nos = [];
    while ($row = $resultado->fetch_assoc()) {
        $nos[(int) $row["id_no"]] = $row;
    }
    $stmt->close();

    if (count($nos) !== 2) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Origem e destino precisam pertencer a instituicao selecionada.",
            "data" => [],
        ];
    } else {
        $distancia = distancia_metros(
            (float) $nos[$id_no_origem]["latitude"],
            (float) $nos[$id_no_origem]["longitude"],
            (float) $nos[$id_no_destino]["latitude"],
            (float) $nos[$id_no_destino]["longitude"]
        );

        $stmt = $conexao->prepare("
            SELECT id_aresta
            FROM Mapa_Aresta
            WHERE id_instituicao = ?
              AND ((id_no_origem = ? AND id_no_destino = ?) OR (id_no_origem = ? AND id_no_destino = ?))
        ");
        $stmt->bind_param("iiiii", $id_instituicao, $id_no_origem, $id_no_destino, $id_no_destino, $id_no_origem);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Esta conexao ja existe.",
                "data" => [],
            ];
            $stmt->close();
        } else {
            $stmt->close();
            $stmt = $conexao->prepare("
                INSERT INTO Mapa_Aresta (id_instituicao, id_no_origem, id_no_destino, distancia_metros)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiid", $id_instituicao, $id_no_origem, $id_no_destino, $distancia);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $retorno = [
                    "status" => "ok",
                    "mensagem" => "Conexao cadastrada com sucesso.",
                    "data" => [["id_aresta" => (int) $conexao->insert_id, "distancia_metros" => $distancia]],
                ];
            } else {
                $retorno = [
                    "status" => "not ok",
                    "mensagem" => "Nao foi possivel cadastrar a conexao.",
                    "data" => [],
                ];
            }

            $stmt->close();
        }
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
