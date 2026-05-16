<?php
session_start();
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$cnpj = isset($_POST["cnpj"]) ? preg_replace("/\D/", "", (string) $_POST["cnpj"]) : "";
$senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";

if ($cnpj === "" || $senha === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "Informe CNPJ e senha.";
} else if (!cnpj_valido($cnpj)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "CNPJ invalido.";
} else {
    $stmt = $conexao->prepare("SELECT id_organizacao, nome, cnpj, senha FROM Organizacao WHERE cnpj = ?");
    $stmt->bind_param("s", $cnpj);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $organizacao = $resultado->fetch_assoc();

        if (password_verify($senha, $organizacao["senha"])) {
            session_regenerate_id(true);

            $_SESSION["organizacao_logada"] = true;
            $_SESSION["organizacao_id"] = $organizacao["id_organizacao"];
            $_SESSION["organizacao_nome"] = $organizacao["nome"];
            $_SESSION["organizacao_cnpj"] = $organizacao["cnpj"];
            $_SESSION["ultima_atividade"] = time();

            unset($organizacao["senha"]);
            $retorno["status"] = "ok";
            $retorno["mensagem"] = "Acesso validado com sucesso.";
            $retorno["data"] = [$organizacao];
        } else {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "CNPJ ou senha invalidos.";
        }
    } else {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "CNPJ ou senha invalidos.";
    }

    $stmt->close();
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
