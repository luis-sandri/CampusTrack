USE campustrack_test;

DELETE FROM Comentario;
DELETE FROM Favorito;
DELETE FROM Evento;
DELETE FROM Mapa_Aresta;
DELETE FROM Mapa_No;
DELETE FROM Locais;
DELETE FROM Organizador;
DELETE FROM Administrador;
DELETE FROM Gerente_Locais;
DELETE FROM Aluno;
DELETE FROM Organizacao;
DELETE FROM Instituicao;
DELETE FROM Usuario;

INSERT INTO Usuario (id_usuario, nome, email, senha) VALUES
    (1, 'Administrador Teste', 'admin@campustrack.test', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.'),
    (2, 'Gerente Teste A', 'gerente.a@campustrack.test', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.'),
    (3, 'Aluno Teste A', 'aluno.a@campustrack.test', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.'),
    (4, 'Organizador Teste', 'organizador@campustrack.test', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.'),
    (5, 'Aluno Teste B', 'aluno.b@campustrack.test', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.');

INSERT INTO Organizacao (id_organizacao, nome, cnpj, senha) VALUES
    (1, 'Organizacao Teste', '11222333000181', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.');

INSERT INTO Instituicao (id_instituicao, nome) VALUES
    (1, 'Instituicao Teste A'),
    (2, 'Instituicao Teste B'),
    (3, 'Instituicao Teste C');

INSERT INTO Administrador (id_adm, CPF, telefone) VALUES
    (1, '12345678909', '41999999999');

INSERT INTO Gerente_Locais (id_gerente, id_instituicao, escola) VALUES
    (2, 1, 'Tecnologia');

INSERT INTO Aluno (id_aluno, id_instituicao, curso) VALUES
    (3, 1, 'Sistemas de Informacao'),
    (5, 1, 'Engenharia de Software');

INSERT INTO Organizador (id_organizador, id_usuario, id_organizacao) VALUES
    (1, 4, 1);

INSERT INTO Locais (id_local, id_instituicao, tipo_escola, nome, capacidade, tipo, longitude, latitude) VALUES
    (1, 1, 'Tecnologia', 'Local A1', 30, 'Laboratorio', -49.250000000000000, -25.450000000000000),
    (2, 1, 'Tecnologia', 'Local A2', 60, 'Sala', -49.251000000000000, -25.451000000000000),
    (3, 1, 'Tecnologia', 'Local A3', 120, 'Auditorio', -49.252000000000000, -25.452000000000000),
    (4, 1, 'Tecnologia', 'Local A4', 45, 'Sala', -49.253000000000000, -25.453000000000000),
    (5, 2, 'Tecnologia', 'Local B1', 30, 'Laboratorio', -49.260000000000000, -25.444000000000000),
    (6, 3, 'Humanas', 'Local C1', 60, 'Sala', -49.261000000000000, -25.445000000000000);

INSERT INTO Mapa_No (id_no, id_instituicao, nome, longitude, latitude) VALUES
    (1, 1, 'No 1', -49.250000000000000, -25.450000000000000),
    (2, 1, 'No 2', -49.250500000000000, -25.450500000000000),
    (3, 1, 'No 3', -49.251000000000000, -25.451000000000000),
    (4, 1, 'No 4', -49.251500000000000, -25.451500000000000),
    (5, 1, 'No 5', -49.252000000000000, -25.452000000000000),
    (6, 1, 'No 6', -49.252500000000000, -25.452500000000000),
    (7, 1, 'No 7', -49.253000000000000, -25.453000000000000),
    (8, 1, 'No 8', -49.253500000000000, -25.453500000000000),
    (9, 1, 'No 9', -49.254000000000000, -25.454000000000000),
    (10, 1, 'No 10', -49.254500000000000, -25.454500000000000);

INSERT INTO Mapa_Aresta (id_aresta, id_instituicao, id_no_origem, id_no_destino, distancia_metros) VALUES
    (1, 1, 1, 2, 75.00),
    (2, 1, 2, 3, 75.00),
    (3, 1, 3, 4, 75.00),
    (4, 1, 4, 5, 75.00),
    (5, 1, 5, 6, 75.00),
    (6, 1, 6, 7, 75.00),
    (7, 1, 7, 8, 75.00),
    (8, 1, 8, 9, 75.00),
    (9, 1, 9, 10, 75.00);

INSERT INTO Evento (id_evento, nome, data, status, id_local, id_organizacao, id_organizador) VALUES
    (1, 'Evento Conflito Base', '2026-10-07 14:00:00', 'ativo', 3, 1, 1),
    (2, 'Evento Secundario', '2026-10-08 10:00:00', 'ativo', 1, 1, 1);

