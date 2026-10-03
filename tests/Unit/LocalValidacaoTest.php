<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/php/locais/validacoes.php';

class LocalValidacaoTest extends TestCase
{
    public function testCT04AceitarLatitudeNoLimite90(): void
    {
        $dados = [
            "id_instituicao" => "1",
            "tipo_escola" => "Tecnologia",
            "tipo" => "Laboratório",
            "nome" => "QA Local",
            "capacidade" => "30",
            "longitude" => "-49.25",
            "latitude" => "90",
        ];

        $erros = [];

        $resultado = local_ler_formulario($dados, $erros);

        $this->assertEmpty($erros);
        $this->assertSame("90", $resultado["latitude"]);
    }
}