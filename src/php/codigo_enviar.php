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
$senha = isset($_POST["senha"]) ? trim((string) $_POST["senha"]) : "";
$id_instituicao_raw = isset($_POST["id_instituicao"]) ? trim((string) $_POST["id_instituicao"]) : "";
$id_instituicao = ctype_digit($id_instituicao_raw) ? (int) $id_instituicao_raw : 0;
$modo = isset($_POST["modo"]) && $_POST["modo"] === "login" ? "login" : "cadastro";

if ($email === "" || $id_instituicao <= 0) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "E-mail e instituicao sao obrigatorios.";
} else if ($modo === "login" && $senha === "") {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "E-mail, senha e instituicao sao obrigatorios.";
} else if (!preg_match("/@pucpr\.edu\.br$/", $email)) {
    $retorno["status"] = "not ok";
    $retorno["mensagem"] = "O e-mail deve ser institucional (@pucpr.edu.br).";
} else {
    $stmt = $conexao->prepare("SELECT id_instituicao FROM Instituicao WHERE id_instituicao = ?");
    $stmt->bind_param("i", $id_instituicao);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows !== 1) {
        $retorno["status"] = "not ok";
        $retorno["mensagem"] = "Instituicao nao encontrada.";
        $stmt->close();
    } else {
        $stmt->close();

        if ($modo === "login") {
            $stmt = $conexao->prepare("
                SELECT u.id_usuario, u.senha
                FROM Usuario u
                INNER JOIN Aluno a ON a.id_aluno = u.id_usuario
                WHERE u.email = ? AND a.id_instituicao = ?
            ");
            $stmt->bind_param("si", $email, $id_instituicao);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows !== 1) {
                $retorno["status"] = "not ok";
                $retorno["mensagem"] = "E-mail ou senha invalidos.";
            } else {
                $aluno = $resultado->fetch_assoc();

                if (!password_verify($senha, $aluno["senha"])) {
                    $retorno["status"] = "not ok";
                    $retorno["mensagem"] = "E-mail ou senha invalidos.";
                }
            }

            $stmt->close();
        }

        if ($retorno["status"] === "") {
            $codigo = rand(100000, 999999);

            $_SESSION["codigo_2fa_" . $email] = $codigo;
            $_SESSION["2fa_expires_" . $email] = time() + (15 * 60);
            $_SESSION["aluno_id_instituicao"] = $id_instituicao;

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'Campustrackbr@gmail.com';
                $mail->Password = 'vtax copa hxps dzxo';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->CharSet = 'UTF-8';

                $mail->setFrom('Campustrackbr@gmail.com', 'CampusTrack Estudante');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Seu Codigo de Acesso - CampusTrack';
                $mail->Body = '
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                        <h2 style="color: #1e40af; text-align: center;">Codigo de Acesso CampusTrack</h2>
                        <p style="font-size: 16px; color: #333;">Ola,</p>
                        <p style="font-size: 16px; color: #333;">Voce solicitou acesso a plataforma CampusTrack. Use o codigo abaixo para validar seu login:</p>
                        <div style="text-align: center; margin: 30px 0;">
                            <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e40af; background: #f3f4f6; padding: 15px 30px; border-radius: 8px;">' . $codigo . '</span>
                        </div>
                        <p style="font-size: 14px; color: #666;">Este codigo expira em 15 minutos. Caso nao tenha solicitado, ignore este e-mail.</p>
                    </div>
                ';
                $mail->AltBody = "Seu Codigo de Acesso ao CampusTrack e: $codigo. Este codigo expira em 15 minutos.";

                $mail->send();

                $retorno["status"] = "ok";
                $retorno["mensagem"] = "Codigo enviado com sucesso.";
            } catch (Exception $e) {
                $retorno["status"] = "not ok";
                $retorno["mensagem"] = "Nao foi possivel enviar o codigo. Tente novamente mais tarde.";
                // $retorno["mensagem"] = "Erro do Mailer: {$mail->ErrorInfo}"; // Para depurar
            }
        }
    }
}

header("Content-type:application/json;charset=utf-8");
echo json_encode($retorno);
