<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Mutation\Scenarios;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . "/fixtures/validacoes_id_decimal.php";

final class Ct22MutantScenario extends TestCase
{
    public function testCt20RejeitaIdInstituicaoDecimal(): void
    {
        $dados = [
            "id" => "1.5",
        ];
        $erros = [];

        $id = \instituicao_ler_id($dados, $erros);

        self::assertSame(0, $id);
        self::assertSame(
            ["ID da instituicao deve ser um numero inteiro positivo."],
            $erros
        );
    }
}