<?php
session_start();
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status"   => "",
    "mensagem" => ""
];

$cnpj_raw       = isset($_POST["cnpj"])       ? preg_replace("/\D/", "", (string) $_POST["cnpj"]) : "";
$codigo_inserido = isset($_POST["codigo"])     ? trim((string) $_POST["codigo"]) : "";
$senha_nova      = isset($_POST["senha_nova"]) ? trim((string) $_POST["senha_nova"]) : "";

if ($cnpj_raw === "" || $codigo_inserido === "" || $senha_nova === "") {
    $retorno["status"]   = "not ok";
    $retorno["mensagem"] = "CNPJ, código e nova senha são obrigatórios.";
} elseif (!senha_valida($senha_nova)) {
    $retorno["status"]   = "not ok";
    $retorno["mensagem"] = senha_mensagem();
} elseif (!isset($_SESSION["codigo_recuperacao_org_" . $cnpj_raw])) {
    $retorno["status"]   = "not ok";
    $retorno["mensagem"] = "Nenhum código ativo encontrado. Solicite novamente.";
} elseif (time() > $_SESSION["recuperacao_expires_org_" . $cnpj_raw]) {
    $retorno["status"]   = "not ok";
    $retorno["mensagem"] = "O código expirou. Solicite um novo código.";
    unset($_SESSION["codigo_recuperacao_org_" . $cnpj_raw]);
    unset($_SESSION["recuperacao_expires_org_" . $cnpj_raw]);
    unset($_SESSION["recuperacao_id_org_" . $cnpj_raw]);
} else {
    $codigo_salvo = (string) $_SESSION["codigo_recuperacao_org_" . $cnpj_raw];
    $id_org       = (int)    $_SESSION["recuperacao_id_org_" . $cnpj_raw];

    if ($codigo_inserido !== $codigo_salvo) {
        $retorno["status"]   = "not ok";
        $retorno["mensagem"] = "Código incorreto. Tente novamente.";
    } else {
        $senha_hash = senha_hash($senha_nova);
        $stmt = $conexao->prepare("UPDATE Organizacao SET senha = ? WHERE id_organizacao = ?");
        $stmt->bind_param("si", $senha_hash, $id_org);

        if ($stmt->execute()) {
            $retorno["status"]   = "ok";
            $retorno["mensagem"] = "Senha redefinida com sucesso.";
            unset($_SESSION["codigo_recuperacao_org_" . $cnpj_raw]);
            unset($_SESSION["recuperacao_expires_org_" . $cnpj_raw]);
            unset($_SESSION["recuperacao_id_org_" . $cnpj_raw]);
        } else {
            $retorno["status"]   = "not ok";
            $retorno["mensagem"] = "Erro ao atualizar a senha no banco de dados.";
        }
        $stmt->close();
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
