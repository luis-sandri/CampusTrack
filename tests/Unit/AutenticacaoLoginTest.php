<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/autenticacao/validacoes.php";

final class AutenticacaoLoginTest extends TestCase
{
    public function testCt11RejeitaEmailInvalidoNoLogin(): void
    {
        $dados = [
            "email" => "sem-arroba",
            "senha" => "Abcdef1!",
        ];
        $erros = [];

        $login = \autenticacao_ler_login($dados, $erros);

        self::assertSame("sem-arroba", $login["email"]);
        self::assertSame("Abcdef1!", $login["senha"]);
        self::assertSame(["E-mail invalido."], $erros);
    }

    public function testCt12RejeitaSenhaVaziaNoLogin(): void
    {
        $dados = [
            "email" => "qa@example.test",
            "senha" => "",
        ];
        $erros = [];

        $login = \autenticacao_ler_login($dados, $erros);

        self::assertSame("qa@example.test", $login["email"]);
        self::assertSame("", $login["senha"]);
        self::assertSame(["Senha e obrigatorio."], $erros);
    }
}
