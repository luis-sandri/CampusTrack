<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/instituicoes/validacoes.php";

final class Ct20InstituicaoIdInvalidoTest extends TestCase
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