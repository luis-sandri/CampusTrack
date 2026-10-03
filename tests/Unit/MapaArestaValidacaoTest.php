<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . "/src/php/mapa/validacoes.php";

final class MapaArestaValidacaoTest extends TestCase
{
    public function testCt33AceitaConexaoEntrePontosDistintos(): void
    {
        $erros = [];
        $aresta = \mapa_aresta_ler_formulario(
            [
                "id_instituicao" => "1",
                "id_no_origem" => "10",
                "id_no_destino" => "11",
            ],
            $erros
        );

        self::assertSame(
            [
                "id_instituicao" => 1,
                "id_no_origem" => 10,
                "id_no_destino" => 11,
            ],
            $aresta
        );
        self::assertSame([], $erros);
    }
}