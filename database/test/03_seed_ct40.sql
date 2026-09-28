USE campustrack_test;

DELETE FROM Favorito
WHERE id_local = 1
  AND id_aluno IN (3, 5);

INSERT INTO Favorito (id_aluno, id_local)
VALUES (5, 1);

