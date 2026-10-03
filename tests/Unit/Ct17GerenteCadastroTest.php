<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/gerentes/validacoes.php";

final class Ct17GerenteCadastroTest extends TestCase
{
    public function testCt17AceitaGerenteComDadosValidos(): void
    {
        $dados = [
            "nome" => "QA Gerente",
            "email" => "qa@example.test",
            "id_instituicao" => "1",
            "escola" => "Tecnologia",
            "senha" => "Abcdef1!",
        ];
        $erros = [];

        $gerente = \gerente_ler_cadastro($dados, $erros);

        self::assertSame([], $erros);
        self::assertSame("QA Gerente", $gerente["nome"]);
        self::assertSame("qa@example.test", $gerente["email"]);
        self::assertSame(1, $gerente["id_instituicao"]);
        self::assertSame("Tecnologia", $gerente["escola"]);
        self::assertSame("Abcdef1!", $gerente["senha"]);
    }
}