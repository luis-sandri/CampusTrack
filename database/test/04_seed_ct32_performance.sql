USE campustrack_test;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Comentario;
TRUNCATE TABLE Evento;
DELETE FROM Favorito WHERE id_local BETWEEN 7 AND 100;
DELETE FROM Locais WHERE id_local BETWEEN 7 AND 100;
SET FOREIGN_KEY_CHECKS = 1;

DROP PROCEDURE IF EXISTS seed_ct32_performance;

DELIMITER //
CREATE PROCEDURE seed_ct32_performance()
BEGIN
    DECLARE contador INT DEFAULT 7;

    WHILE contador <= 100 DO
        INSERT INTO Locais (
            id_local,
            id_instituicao,
            tipo_escola,
            nome,
            capacidade,
            tipo,
            longitude,
            latitude
        ) VALUES (
            contador,
            1,
            'Performance',
            CONCAT('Local Performance ', contador),
            100,
            'Sala',
            -49.250000000000000 - (contador / 1000000),
            -25.450000000000000 - (contador / 1000000)
        );

        SET contador = contador + 1;
    END WHILE;

    SET contador = 1;

    WHILE contador <= 10000 DO
        INSERT INTO Evento (
            id_evento,
            nome,
            data,
            status,
            id_local,
            id_organizacao,
            id_organizador
        ) VALUES (
            1000 + contador,
            CONCAT('Evento Performance ', contador),
            DATE_ADD('2026-01-01 00:00:00', INTERVAL contador MINUTE),
            'ativo',
            MOD(contador - 1, 100) + 1,
            1,
            1
        );

        SET contador = contador + 1;
    END WHILE;
END//
DELIMITER ;

CALL seed_ct32_performance();
DROP PROCEDURE seed_ct32_performance;

