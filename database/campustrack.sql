-- Modelo físico do banco de dados
CREATE DATABASE campustrack;
USE campustrack;

CREATE TABLE IF NOT EXISTS Usuario (
    id_usuario  INT          PRIMARY KEY AUTO_INCREMENT,
    nome        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL,
    senha       VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_usuario_email (email)
);

CREATE TABLE IF NOT EXISTS Organizacao (
    id_organizacao INT          PRIMARY KEY AUTO_INCREMENT,
    nome           VARCHAR(255) NOT NULL,    
    cnpj           VARCHAR(14)  NOT NULL,
    senha          VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_organizacao_cnpj (cnpj)
);

CREATE TABLE IF NOT EXISTS Instituicao (
    id_instituicao INT          PRIMARY KEY AUTO_INCREMENT,
    nome           VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS Aluno (
    id_aluno       INT         PRIMARY KEY AUTO_INCREMENT,
    id_instituicao INT         NOT NULL,
    curso          VARCHAR(80) NOT NULL,
    CONSTRAINT fk_aluno_usuario
        FOREIGN KEY (id_aluno)
        REFERENCES Usuario(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_aluno_instituicao
        FOREIGN KEY (id_instituicao)
        REFERENCES Instituicao(id_instituicao)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Gerente_Locais (
    id_gerente     INT         PRIMARY KEY AUTO_INCREMENT,
    id_instituicao INT         NOT NULL,
    escola         VARCHAR(30) NOT NULL,
    CONSTRAINT fk_gerente_usuario
        FOREIGN KEY (id_gerente)
        REFERENCES Usuario(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_gerente_instituicao
        FOREIGN KEY (id_instituicao)
        REFERENCES Instituicao(id_instituicao)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Administrador (
    id_adm    INT         PRIMARY KEY AUTO_INCREMENT,
    CPF       VARCHAR(11) NOT NULL,
    telefone  VARCHAR(11) NOT NULL,
    UNIQUE KEY unique_administrador_cpf (CPF),
    CONSTRAINT fk_adm_usuario
        FOREIGN KEY (id_adm)
        REFERENCES Usuario(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Organizador (
    id_organizador INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_usuario     INT NOT NULL,
    id_organizacao INT NOT NULL,
    CONSTRAINT fk_organizador_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES Usuario(id_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_organizador_organizacao
        FOREIGN KEY (id_organizacao)
        REFERENCES Organizacao(id_organizacao)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Locais (
    id_local       INT          PRIMARY KEY AUTO_INCREMENT,
    id_instituicao INT          NOT NULL,
    tipo_escola    VARCHAR(50)  NOT NULL,
    nome           VARCHAR(255) NOT NULL,
    capacidade     INT          NOT NULL,
    tipo           VARCHAR(50)  NOT NULL,
    longitude      VARCHAR(10)  NOT NULL,
    latitude       VARCHAR(8)   NOT NULL,
    CONSTRAINT fk_locais_instituicao
        FOREIGN KEY (id_instituicao)
        REFERENCES Instituicao(id_instituicao)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Evento (
    id_evento      INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    data DATETIME NOT NULL,
    status VARCHAR(10) NOT NULL,
    id_local       INT NOT NULL,
    id_organizacao INT NOT NULL,
    id_organizador INT NOT NULL,
    CONSTRAINT fk_evento_local
        FOREIGN KEY (id_local)
        REFERENCES Locais(id_local)
        ON DELETE CASCADE,
    CONSTRAINT fk_evento_organizacao
        FOREIGN KEY (id_organizacao)
        REFERENCES Organizacao(id_organizacao)
        ON DELETE CASCADE,
    CONSTRAINT fk_evento_organizador
        FOREIGN KEY (id_organizador)
        REFERENCES Organizador(id_organizador)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Comentario (
    id_comentario INT PRIMARY KEY AUTO_INCREMENT,
    id_aluno      INT          NOT NULL,
    id_evento     INT          NOT NULL,
    comentario    VARCHAR(140) NOT NULL,
    CONSTRAINT fk_comentario_aluno
        FOREIGN KEY (id_aluno)
        REFERENCES Aluno(id_aluno)
        ON DELETE CASCADE,
    CONSTRAINT fk_comentario_evento
        FOREIGN KEY (id_evento)
        REFERENCES Evento(id_evento)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Favorito (
    id_favorito INT PRIMARY KEY AUTO_INCREMENT,
    id_aluno    INT NOT NULL,
    id_local    INT NOT NULL,
    CONSTRAINT fk_favorito_aluno
        FOREIGN KEY (id_aluno)
        REFERENCES Aluno(id_aluno)
        ON DELETE CASCADE,
    CONSTRAINT fk_favorito_local
        FOREIGN KEY (id_local)
        REFERENCES Locais(id_local)
        ON DELETE CASCADE,
    UNIQUE KEY unique_aluno_local (id_aluno, id_local)
);

-- Populacao simples para testes
INSERT INTO Usuario (id_usuario, nome, email, senha) VALUES
    (1, 'Administrador Teste', 'admin@campustrack.com', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.'),
    (2, 'Gerente Teste', 'gerente@campustrack.com', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.'),
    (3, 'Aluno Teste', 'aluno@pucpr.edu.br', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.'),
    (4, 'Organizador Teste', 'organizador@campustrack.com', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.')
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    email = VALUES(email),
    senha = VALUES(senha);

INSERT INTO Organizacao (id_organizacao, nome, cnpj, senha) VALUES
    (1, 'Organizacao Teste', '11222333000181', '$2y$10$kmEF/BkfRsUGyMXxzMP14u7C0yDyHHeqCAt5oBDzKBlsUk4S.aZl.')
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    cnpj = VALUES(cnpj),
    senha = VALUES(senha);

INSERT INTO Instituicao (id_instituicao, nome) VALUES
    (1, 'PUCPR Curitiba'),
    (2, 'Instituto Tecnologico Regional'),
    (3, 'Faculdade Municipal do Norte')
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome);

INSERT INTO Administrador (id_adm, CPF, telefone) VALUES
    (1, '12345678909', '41999999999')
ON DUPLICATE KEY UPDATE
    CPF = VALUES(CPF),
    telefone = VALUES(telefone);

INSERT INTO Gerente_Locais (id_gerente, id_instituicao, escola) VALUES
    (2, 1, 'Politecnica')
ON DUPLICATE KEY UPDATE
    id_instituicao = VALUES(id_instituicao),
    escola = VALUES(escola);

INSERT INTO Aluno (id_aluno, id_instituicao, curso) VALUES
    (3, 1, 'Sistemas de Informacao')
ON DUPLICATE KEY UPDATE
    id_instituicao = VALUES(id_instituicao),
    curso = VALUES(curso);

INSERT INTO Organizador (id_organizador, id_usuario, id_organizacao) VALUES
    (1, 4, 1)
ON DUPLICATE KEY UPDATE
    id_usuario = VALUES(id_usuario),
    id_organizacao = VALUES(id_organizacao);

INSERT INTO Locais (id_local, id_instituicao, tipo_escola, nome, capacidade, tipo, longitude, latitude) VALUES
    (1, 1, 'Politecnica', 'Bloco 1', 120, 'Sala', '-49.2577', '-25.4420'),
    (2, 1, 'Politecnica', 'Laboratorio de Informatica', 40, 'Laboratorio', '-49.2580', '-25.4425'),
    (3, 1, 'Saude', 'Auditorio Central', 250, 'Auditorio', '-49.2584', '-25.4430'),
    (4, 2, 'Tecnologia', 'Sala Maker', 30, 'Laboratorio', '-49.2600', '-25.4440'),
    (5, 3, 'Humanas', 'Sala 101', 60, 'Sala', '-49.2610', '-25.4450')
ON DUPLICATE KEY UPDATE
    id_instituicao = VALUES(id_instituicao),
    tipo_escola = VALUES(tipo_escola),
    nome = VALUES(nome),
    capacidade = VALUES(capacidade),
    tipo = VALUES(tipo),
    longitude = VALUES(longitude),
    latitude = VALUES(latitude);

INSERT INTO Evento (id_evento, nome, data, status, id_local, id_organizacao, id_organizador) VALUES
    (1, 'Feira de Carreiras', '2026-06-15 19:00:00', 'ativo', 1, 1, 1),
    (2, 'Palestra Tech', '2026-06-20 14:00:00', 'ativo', 3, 1, 1)
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    data = VALUES(data),
    status = VALUES(status),
    id_local = VALUES(id_local),
    id_organizacao = VALUES(id_organizacao),
    id_organizador = VALUES(id_organizador);
