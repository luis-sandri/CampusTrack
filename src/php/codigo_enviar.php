<?php
session_start();
include_once __DIR__ . "/conexao.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/libs/PHPMailer/Exception.php';
require __DIR__ . '/libs/PHPMailer/PHPMailer.php';
require __DIR__ . '/libs/PHPMailer/SMTP.php';

$retorno = [
    "status" => "",
    "mensagem" => "",
    "data" => [],
];

$email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";
$id_instituicao = isset($_POST["id_instituicao"]) ? trim((string) $_POST["id_instituicao"]) : "";

if ($email === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "E-mail não informado.";
} else if (!preg_match("/@pucpr\.edu\.br$/", $email)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "O e-mail deve ser institucional (@pucpr.edu.br).";
} else {
    // Gerar código de 6 dígitos
    $codigo = rand(100000, 999999);

    // Salvar na sessão
    $_SESSION["codigo_2fa_" . $email] = $codigo;
    $_SESSION["2fa_expires_" . $email] = time() + (15 * 60); // 15 minutos de expiração

    $mail = new PHPMailer(true);

    try {
        // Configuração do Servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'Campustrackbr@gmail.com';
        $mail->Password = 'vtax copa hxps dzxo';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587; // TLS
        $mail->CharSet = 'UTF-8';

        // Destinatários
        $mail->setFrom('Campustrackbr@gmail.com', 'CampusTrack Estudante');
        $mail->addAddress($email);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Seu Código de Acesso - CampusTrack';
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                <h2 style="color: #1e40af; text-align: center;">Código de Acesso CampusTrack</h2>
                <p style="font-size: 16px; color: #333;">Olá,</p>
                <p style="font-size: 16px; color: #333;">Você solicitou acesso à plataforma CampusTrack. Use o código abaixo para validar seu login:</p>
                <div style="text-align: center; margin: 30px 0;">
                    <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e40af; background: #f3f4f6; padding: 15px 30px; border-radius: 8px;">' . $codigo . '</span>
                </div>
                <p style="font-size: 14px; color: #666;">Este código expira em 15 minutos. Caso não tenha solicitado, ignore este e-mail.</p>
            </div>
        ';
        $mail->AltBody = "Seu Código de Acesso ao CampusTrack é: $codigo . Este código expira em 15 minutos.";

        $mail->send();

        $retorno["status"] = "ok";
        $retorno["mensagem"] = "Código enviado com sucesso.";

    } catch (Exception $e) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Não foi possível enviar o código. Tente novamente mais tarde.";
        // $retorno["mensagem"] = "Erro do Mailer: {$mail->ErrorInfo}"; // Para depurar
    }
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
