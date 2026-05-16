<?php
include_once __DIR__ . "/valida_sessao_admin.php";
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$nome = isset($_POST["nome"]) ? trim((string) $_POST["nome"]) : "";
$email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";
$senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";
$id_instituicao_raw = isset($_POST["id_instituicao"]) ? trim((string) $_POST["id_instituicao"]) : "";
$id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;
$escola = isset($_POST["escola"]) ? trim((string) $_POST["escola"]) : "";

if ($nome === "" || $email === "" || $senha === "" || $id_instituicao <= 0 || $escola === "") {
    $retorno = [
        "status" => "not ok",
        "mensagem" => "Nome, e-mail, senha, instituicao e escola sao obrigatorios.",
        "data" => [],
    ];
} else if (!senha_valida($senha)) {
    $retorno = [
        "status" => "not ok",
        "mensagem" => senha_mensagem(),
        "data" => [],
    ];
} else {
    $stmt = $conexao->prepare("SELECT id_usuario FROM Usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $retorno = [
            "status" => "not ok",
            "mensagem" => "Ja existe um usuario cadastrado com este e-mail.",
            "data" => [],
        ];
        $stmt->close();
    } else {
        $stmt->close();
        $conexao->begin_transaction();

        try {
            $senha = senha_hash($senha);
            $stmt = $conexao->prepare("INSERT INTO Usuario (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senha);
            $stmt->execute();
            $id_usuario = (int) $conexao->insert_id;
            $stmt->close();

            $stmt = $conexao->prepare("INSERT INTO Gerente_Locais (id_gerente, id_instituicao, escola) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $id_usuario, $id_instituicao, $escola);
            $stmt->execute();
            $stmt->close();

            $conexao->commit();

            $retorno = [
                "status" => "ok",
                "mensagem" => "Gerente cadastrado com sucesso.",
                "data" => [["id_usuario" => $id_usuario]],
            ];
        } catch (Throwable $e) {
            $conexao->rollback();
            $retorno = [
                "status" => "not ok",
                "mensagem" => "Nao foi possivel cadastrar o gerente.",
                "data" => [],
            ];
        }
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
