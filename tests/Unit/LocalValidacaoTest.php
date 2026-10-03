<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../src/php/locais/validacoes.php';

class LocalValidacaoTest extends TestCase
{
    public function testCT03RejeitarCapacidadeZero(): void
    {
        $dados = [
            "id_instituicao" => "1",
            "tipo_escola" => "Tecnologia",
            "tipo" => "Laboratório",
            "nome" => "QA Local",
            "capacidade" => "0",
            "longitude" => "-49.25",
            "latitude" => "-25.45",
        ];

        $erros = [];

        $resultado = local_ler_formulario($dados, $erros);

        $this->assertSame(0, $resultado["capacidade"]);
        $this->assertCount(1, $erros);
        $this->assertContains(
            "Capacidade deve ser um numero inteiro positivo.",
            $erros
        );
    }
}