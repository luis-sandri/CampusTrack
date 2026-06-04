<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

session_start();
include_once __DIR__ . "/../core/conexao.php";
require_once __DIR__ . "/../core/validacoes.php";
require_once __DIR__ . "/repositorio.php";

require __DIR__ . "/../libs/PHPMailer/Exception.php";
require __DIR__ . "/../libs/PHPMailer/PHPMailer.php";
require __DIR__ . "/../libs/PHPMailer/SMTP.php";

$perfil = isset($_POST["perfil"]) ? trim((string) $_POST["perfil"]) : "gerente_organizador";
$erros = [];

if ($perfil === "aluno") {
    $email = campo_email_obrigatorio($_POST, "email", "E-mail", $erros);

    if ($email !== "" && !preg_match("/@pucpr\.edu\.br$/", $email)) {
        $erros[] = "O e-mail deve ser institucional (@pucpr.edu.br).";
    }

    if (count($erros) > 0) {
        responder_json(resposta_validacao($erros));
    }

    $id_usuario = recuperacao_aluno_id_por_email($conexao, $email);

    if ($id_usuario === null) {
        responder_json(resposta_erro("Nao foi possivel validar o e-mail."));
    }

    if ($id_usuario === 0) {
        responder_json(resposta_erro("E-mail nao encontrado ou nao pertence a um estudante."));
    }

    responder_json(recuperacao_enviar_codigo_usuario($id_usuario, $email));
}

if ($perfil === "organizacao") {
    $cnpj = preg_replace("/\D/", "", campo_texto_obrigatorio($_POST, "cnpj", "CNPJ", $erros));
    $email_contato = campo_email_obrigatorio($_POST, "email", "E-mail de contato", $erros);

    if ($cnpj !== "" && !cnpj_valido($cnpj)) {
        $erros[] = "CNPJ invalido.";
    }

    if (count($erros) > 0) {
        responder_json(resposta_validacao($erros));
    }

    $id_organizacao = recuperacao_organizacao_id_por_cnpj($conexao, $cnpj);

    if ($id_organizacao === null) {
        responder_json(resposta_erro("Nao foi possivel validar o CNPJ."));
    }

    if ($id_organizacao === 0) {
        responder_json(resposta_erro("CNPJ nao encontrado."));
    }

    responder_json(recuperacao_enviar_codigo_organizacao($id_organizacao, $cnpj, $email_contato));
}

$email = campo_email_obrigatorio($_POST, "email", "E-mail", $erros);

if (count($erros) > 0) {
    responder_json(resposta_validacao($erros));
}

$id_usuario = recuperacao_usuario_id_por_email($conexao, $email);

if ($id_usuario === null) {
    responder_json(resposta_erro("Nao foi possivel validar o e-mail."));
}

if ($id_usuario === 0) {
    responder_json(resposta_erro("E-mail nao encontrado ou nao tem permissao de gerente/organizador."));
}

responder_json(recuperacao_enviar_codigo_usuario($id_usuario, $email));

function recuperacao_enviar_codigo_usuario(int $id_usuario, string $email): array
{
    $codigo = random_int(100000, 999999);
    $_SESSION["codigo_recuperacao_" . $email] = $codigo;
    $_SESSION["recuperacao_expires_" . $email] = time() + (15 * 60);
    $_SESSION["recuperacao_id_usuario_" . $email] = $id_usuario;

    return recuperacao_enviar_email($email, $codigo);
}

function recuperacao_enviar_codigo_organizacao(int $id_organizacao, string $cnpj, string $email): array
{
    $codigo = random_int(100000, 999999);
    $_SESSION["codigo_recuperacao_org_" . $cnpj] = $codigo;
    $_SESSION["recuperacao_expires_org_" . $cnpj] = time() + (15 * 60);
    $_SESSION["recuperacao_id_org_" . $cnpj] = $id_organizacao;

    return recuperacao_enviar_email($email, $codigo);
}

function recuperacao_enviar_email(string $email, int $codigo): array
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "Campustrackbr@gmail.com";
        $mail->Password = "vtax copa hxps dzxo";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";

        $mail->setFrom("Campustrackbr@gmail.com", "CampusTrack Recuperacao");
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = "Recuperacao de Senha - CampusTrack";
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h2 style="color: #1e40af; text-align: center;">Recuperacao de Senha</h2>
                <p style="font-size: 16px; color: #333;">Ola,</p>
                <p style="font-size: 16px; color: #333;">Use o codigo abaixo para definir uma nova senha no CampusTrack:</p>
                <div style="text-align: center; margin: 30px 0;">
                    <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e40af; background: #f3f4f6; padding: 15px 30px; border-radius: 8px;">' . $codigo . '</span>
                </div>
                <p style="font-size: 14px; color: #666;">Este codigo expira em 15 minutos. Caso nao tenha solicitado, ignore este e-mail.</p>
            </div>
        ';
        $mail->AltBody = "Seu codigo de recuperacao do CampusTrack e: $codigo. Este codigo expira em 15 minutos.";

        $mail->send();
        return resposta_ok("Codigo enviado com sucesso para seu e-mail.");
    } catch (Exception $e) {
        return resposta_erro("Nao foi possivel enviar o codigo. Tente novamente mais tarde.");
    }
}
