CREATE DATABASE campustrack;
USE campustrack;

CREATE TABLE IF NOT EXISTS Usuario (
    id_usuario  INT          PRIMARY KEY AUTO_INCREMENT,
    nome        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL,
    senha       VARCHAR(30)  NOT NULL
);

CREATE TABLE IF NOT EXISTS Organizacao (
    id_organizacao INT          PRIMARY KEY AUTO_INCREMENT,
    nome           VARCHAR(255) NOT NULL,    
    cnpj           VARCHAR(14)  NOT NULL
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
    CONSTRAINT fk_adm_usuario
        FOREIGN KEY (id_adm)
        REFERENCES Usuario(id_usuario)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Organizador (
    id_organizador INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_usuario     INT NOT NULL,
    CONSTRAINT fk_organizador_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES Usuario(id_usuario)
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
