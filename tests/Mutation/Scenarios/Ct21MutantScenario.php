<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Mutation\Scenarios;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . "/fixtures/gerentes_sem_validacao_escola.php";

final class Ct21MutantScenario extends TestCase
{
    public function testCt18RejeitaGerenteSemEscola(): void
    {
        $dados = [
            "nome" => "QA Gerente",
            "email" => "qa@example.test",
            "id_instituicao" => "1",
            "escola" => "",
            "senha" => "Abcdef1!",
        ];
        $erros = [];

        $gerente = \gerente_ler_cadastro($dados, $erros);

        self::assertSame("QA Gerente", $gerente["nome"]);
        self::assertSame("qa@example.test", $gerente["email"]);
        self::assertSame(1, $gerente["id_instituicao"]);
        self::assertSame("", $gerente["escola"]);
        self::assertSame("Abcdef1!", $gerente["senha"]);
        self::assertSame(["Escola e obrigatorio."], $erros);
    }
}