# Testes de desempenho

O CT32 usa o banco exclusivo `campustrack_test` com 10.000 eventos ativos distribuídos por 100 locais. O teste faz cinco aquecimentos e 30 medições com `hrtime(true)`, valida o resultado da consulta e exibe mediana, p95, máximo e `EXPLAIN`.

Prepare o perfil:

```powershell
powershell -ExecutionPolicy Bypass -File database/test/setup_test_database.ps1 -Profile ct32 -Port 3307
$env:CAMPUS_TRACK_TEST_DB_PORT = "3307"
vendor\bin\phpunit --do-not-cache-result tests\Performance\EventoConflitoLocalPerformanceTest.php
```

Use `3306` no lugar de `3307` quando o MariaDB de teste estiver nessa porta. O benchmark informa as medições sem impor um limite de aprovação.
