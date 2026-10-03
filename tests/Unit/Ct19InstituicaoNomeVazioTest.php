<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/instituicoes/validacoes.php";

final class Ct19InstituicaoNomeVazioTest extends TestCase
{
    public function testCt19RejeitaInstituicaoComNomeVazio(): void
    {
        $dados = [
            "nome" => "   ",
        ];
        $erros = [];

        $instituicao = \instituicao_ler_formulario($dados, $erros);

        self::assertSame("", $instituicao["nome"]);
        self::assertSame(["Nome e obrigatorio."], $erros);
    }
}