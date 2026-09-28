<?php

declare(strict_types=1);

namespace CampusTrack\Tests\Support;

abstract class TransactionalDatabaseTestCase extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->conexao->begin_transaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->conexao)) {
            $this->conexao->rollback();
        }

        parent::tearDown();
    }
}

