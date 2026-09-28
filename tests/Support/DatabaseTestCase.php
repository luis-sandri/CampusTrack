<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Support;

use mysqli;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected mysqli $conexao;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conexao = criar_conexao_teste();
    }

    protected function tearDown(): void
    {
        if (isset($this->conexao)) {
            $this->conexao->close();
        }

        parent::tearDown();
    }
}

