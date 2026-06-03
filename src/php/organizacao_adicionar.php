<?php
include_once __DIR__ . "/conexao.php";
include_once __DIR__ . "/validacoes.php";

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$erros = [];
$nome = campo_texto_obrigatorio($_POST, "nome", "Nome", $erros);
$cnpj_raw = campo_texto_obrigatorio($_POST, "cnpj", "CNPJ", $erros);
$cnpj = preg_replace("/\D/", "", $cnpj_raw);
$senha = campo_texto_obrigatorio($_POST, "senha", "Senha", $erros);
$confirmar_senha = campo_texto_obrigatorio($_POST, "confirmar_senha", "Confirmacao de senha", $erros);

if ($cnpj !== "" && !cnpj_valido($cnpj)) {
    $erros[] = "CNPJ invalido.";
}

if ($senha !== "" && !senha_valida($senha)) {
    $erros[] = senha_mensagem();
}

if ($senha !== "" && $confirmar_senha !== "" && $senha !== $confirmar_senha) {
    $erros[] = "As senhas nao coincidem.";
}

if (count($erros) > 0) {
    $retorno = retorno_validacao($erros);
} else {
    $stmt = $conexao->prepare("SELECT id_organizacao FROM Organizacao WHERE cnpj = ?");
    $stmt->bind_param("s", $cnpj);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Ja existe uma organizacao cadastrada com este CNPJ.";
        $stmt->close();
    } else {
        $stmt->close();

        $stmt = $conexao->prepare("INSERT INTO Organizacao (nome, cnpj, senha) VALUES (?, ?, ?)");
        $senha_hash = senha_hash($senha);
        $stmt->bind_param("sss", $nome, $cnpj, $senha_hash);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $retorno["status"] = "ok";
            $retorno["mensagem"] = "Organizacao cadastrada com sucesso.";
            $retorno["data"] = [["id_organizacao" => (int) $conexao->insert_id]];
        } else {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "Nao foi possivel cadastrar a organizacao.";
        }

        $stmt->close();
    }
}

$conexao->close();

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
