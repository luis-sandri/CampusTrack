<?php
include_once __DIR__ . "/valida_sessao_aluno.php";
include_once __DIR__ . "/conexao.php";

$retorno = [
    "status"   => "",
    "mensagem" => "",
    "data"     => [],
];

$id_local_raw = isset($_POST["id_local"]) ? trim((string) $_POST["id_local"]) : "";
$id_local     = ctype_digit($id_local_raw) ? (int) $id_local_raw : 0;
$id_aluno     = (int) $_SESSION["aluno_id"];

if ($id_local <= 0) {
    $retorno = [
        "status"   => "not ok",
        "mensagem" => "Local inválido.",
        "data"     => [],
    ];
} else {
    // Verifica se o local existe
    $stmt = $conexao->prepare("SELECT id_local FROM Locais WHERE id_local = ?");
    $stmt->bind_param("i", $id_local);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        $retorno = [
            "status"   => "not ok",
            "mensagem" => "Local não encontrado.",
            "data"     => [],
        ];
    } else {
        $stmt->close();

        // Insere favorito (IGNORE evita duplicata silenciosamente)
        $stmt = $conexao->prepare(
            "INSERT IGNORE INTO Favorito (id_aluno, id_local) VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $id_aluno, $id_local);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $id_favorito = (int) $conexao->insert_id;
            $retorno = [
                "status"   => "ok",
                "mensagem" => "Local adicionado aos favoritos.",
                "data"     => [["id_favorito" => $id_favorito]],
            ];
        } else {
            // Já estava favoritado (IGNORE não inseriu)
            $retorno = [
                "status"   => "ok",
                "mensagem" => "Local já está nos favoritos.",
                "data"     => [],
            ];
        }

        $stmt->close();
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
