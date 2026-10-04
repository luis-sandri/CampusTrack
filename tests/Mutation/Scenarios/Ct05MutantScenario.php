<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Mutation\Scenarios;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . "/fixtures/validacoes_capacidade_zero.php";

final class Ct05MutantScenario extends TestCase
{
    public function testCT03RejeitarCapacidadeZero(): void
    {
        $dados = [
            "capacidade" => "0",
        ];

        $erros = [];

        $resultado = \campo_inteiro_positivo_obrigatorio(
            $dados,
            "capacidade",
            "Capacidade",
            $erros
        );

        self::assertSame(0, $resultado);
        self::assertCount(1, $erros);
        self::assertContains(
            "Capacidade deve ser um numero inteiro positivo.",
            $erros
        );
    }
}