CREATE DATABASE campustrack;
USE campustrack;

CREATE TABLE Usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(30) NOT NULL
);

CREATE TABLE Instituicao (
    id_instituicao INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL
);


CREATE TABLE Aluno (
    id_aluno INT PRIMARY KEY,
    id_instituicao INT,
    curso VARCHAR(80),

    FOREIGN KEY (id_aluno) REFERENCES Usuario(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_instituicao) REFERENCES Instituicao(id_instituicao)
        ON DELETE CASCADE
);

CREATE TABLE Gerente_locais (
    id_gerente INT PRIMARY KEY,
    id_instituicao INT,
    escola VARCHAR(30),

    FOREIGN KEY (id_gerente) REFERENCES Usuario(id_usuario)
        ON DELETE CASCADE,

    FOREIGN KEY (id_instituicao) REFERENCES Instituicao(id_instituicao)
        ON DELETE CASCADE
);


CREATE TABLE Administrador (
    id_adm INT PRIMARY KEY,
    CPF VARCHAR(11),
    telefone VARCHAR(11),

    FOREIGN KEY (id_adm) REFERENCES Usuario(id_usuario)
        ON DELETE CASCADE
);


CREATE TABLE Organizacao (
    id_organizacao INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255),
    email VARCHAR(255),
    senha VARCHAR(30),
    cnpj VARCHAR(14)
);


CREATE TABLE Locais (
    id_local INT AUTO_INCREMENT PRIMARY KEY,
    id_instituicao INT,
    tipo VARCHAR(50),
    nome VARCHAR(255),
    capacidade INT,
    longitude VARCHAR(10),
    latitude VARCHAR(8),

    FOREIGN KEY (id_instituicao) REFERENCES Instituicao(id_instituicao)
        ON DELETE CASCADE
);


CREATE TABLE Evento (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    id_local INT,
    id_organizacao INT,

    FOREIGN KEY (id_local) REFERENCES Locais(id_local)
        ON DELETE CASCADE,

    FOREIGN KEY (id_organizacao) REFERENCES Organizacao(id_organizacao)
        ON DELETE CASCADE
);
