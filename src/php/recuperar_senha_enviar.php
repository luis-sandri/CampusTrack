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
    "mensagem" => ""
];

$email = isset($_POST["email"]) ? trim((string) $_POST["email"]) : "";

if ($email === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "E-mail é obrigatório.";
} else {
    // Check if the email exists and belongs to Gerente_Locais or Organizador
    $sql = "SELECT u.id_usuario 
            FROM Usuario u 
            LEFT JOIN Gerente_Locais g ON u.id_usuario = g.id_gerente 
            LEFT JOIN Organizador o ON u.id_usuario = o.id_usuario 
            WHERE u.email = ? AND (g.id_gerente IS NOT NULL OR o.id_usuario IS NOT NULL)";
            
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows !== 1) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "E-mail não encontrado ou não tem permissão de Gerente/Organizador.";
        $stmt->close();
    } else {
        $row = $resultado->fetch_assoc();
        $id_usuario = $row["id_usuario"];
        $stmt->close();

        // Gerar código de 6 dígitos
        $codigo = rand(100000, 999999);

        // Salvar na sessão
        $_SESSION["codigo_recuperacao_" . $email] = $codigo;
        $_SESSION["recuperacao_expires_" . $email] = time() + (15 * 60); // 15 minutos de expiração
        $_SESSION["recuperacao_id_usuario_" . $email] = $id_usuario;

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
            $mail->setFrom('Campustrackbr@gmail.com', 'CampusTrack Recuperação');
            $mail->addAddress($email);

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha - CampusTrack';
            $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                    <h2 style="color: #1e40af; text-align: center;">Recuperação de Senha</h2>
                    <p style="font-size: 16px; color: #333;">Olá,</p>
                    <p style="font-size: 16px; color: #333;">Você solicitou a recuperação de senha da sua conta CampusTrack. Use o código abaixo para definir uma nova senha:</p>
                    <div style="text-align: center; margin: 30px 0;">
                        <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e40af; background: #f3f4f6; padding: 15px 30px; border-radius: 8px;">' . $codigo . '</span>
                    </div>
                    <p style="font-size: 14px; color: #666;">Este código expira em 15 minutos. Caso não tenha solicitado, ignore este e-mail.</p>
                </div>
            ';
            $mail->AltBody = "Seu Código de Recuperação do CampusTrack é: $codigo . Este código expira em 15 minutos.";

            $mail->send();

            $retorno["status"] = "ok";
            $retorno["mensagem"] = "Código enviado com sucesso para seu e-mail.";

        } catch (Exception $e) {
            $retorno["status"] = "not ok";
            $retorno["mensagem"] = "Não foi possível enviar o código. Tente novamente mais tarde.";
        }
    }
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
