<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/php/locais/validacoes.php';

class LocalValidacaoTest extends TestCase
{
    public function testCT01AceitarLocalValido(): void
    {
        $dados = [
            "id_instituicao" => "1",
            "tipo_escola" => "Tecnologia",
            "tipo" => "Laboratório",
            "nome" => "QA Local",
            "capacidade" => "30",
            "longitude" => "-49.25",
            "latitude" => "-25.45",
        ];

        $erros = [];

        $resultado = local_ler_formulario($dados, $erros);

        $this->assertEmpty($erros);
        $this->assertSame(30, $resultado["capacidade"]);
        $this->assertSame("QA Local", $resultado["nome"]);
    }
}