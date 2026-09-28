DROP DATABASE IF EXISTS campustrack_test;
CREATE DATABASE campustrack_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE campustrack_test;

CREATE TABLE Usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_usuario_email (email)
);

CREATE TABLE Organizacao (
    id_organizacao INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    cnpj VARCHAR(14) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    UNIQUE KEY unique_organizacao_cnpj (cnpj)
);

CREATE TABLE Instituicao (
    id_instituicao INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL
);

CREATE TABLE Aluno (
    id_aluno INT PRIMARY KEY AUTO_INCREMENT,
    id_instituicao INT NOT NULL,
    curso VARCHAR(80) NOT NULL,
    CONSTRAINT fk_test_aluno_usuario
        FOREIGN KEY (id_aluno) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_test_aluno_instituicao
        FOREIGN KEY (id_instituicao) REFERENCES Instituicao(id_instituicao) ON DELETE CASCADE
);

CREATE TABLE Gerente_Locais (
    id_gerente INT PRIMARY KEY AUTO_INCREMENT,
    id_instituicao INT NOT NULL,
    escola VARCHAR(30) NOT NULL,
    CONSTRAINT fk_test_gerente_usuario
        FOREIGN KEY (id_gerente) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_test_gerente_instituicao
        FOREIGN KEY (id_instituicao) REFERENCES Instituicao(id_instituicao) ON DELETE CASCADE
);

CREATE TABLE Administrador (
    id_adm INT PRIMARY KEY AUTO_INCREMENT,
    CPF VARCHAR(11) NOT NULL,
    telefone VARCHAR(11) NOT NULL,
    UNIQUE KEY unique_administrador_cpf (CPF),
    CONSTRAINT fk_test_adm_usuario
        FOREIGN KEY (id_adm) REFERENCES Usuario(id_usuario) ON DELETE CASCADE
);

CREATE TABLE Organizador (
    id_organizador INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_organizacao INT NOT NULL,
    CONSTRAINT fk_test_organizador_usuario
        FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE,
    CONSTRAINT fk_test_organizador_organizacao
        FOREIGN KEY (id_organizacao) REFERENCES Organizacao(id_organizacao) ON DELETE CASCADE
);

CREATE TABLE Locais (
    id_local INT PRIMARY KEY AUTO_INCREMENT,
    id_instituicao INT NOT NULL,
    tipo_escola VARCHAR(50) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    capacidade INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    longitude DECIMAL(18,15) NOT NULL,
    latitude DECIMAL(18,15) NOT NULL,
    CONSTRAINT fk_test_locais_instituicao
        FOREIGN KEY (id_instituicao) REFERENCES Instituicao(id_instituicao) ON DELETE CASCADE
);

CREATE TABLE Mapa_No (
    id_no INT PRIMARY KEY AUTO_INCREMENT,
    id_instituicao INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    longitude DECIMAL(18,15) NOT NULL,
    latitude DECIMAL(18,15) NOT NULL,
    CONSTRAINT fk_test_mapa_no_instituicao
        FOREIGN KEY (id_instituicao) REFERENCES Instituicao(id_instituicao) ON DELETE CASCADE
);

CREATE TABLE Mapa_Aresta (
    id_aresta INT PRIMARY KEY AUTO_INCREMENT,
    id_instituicao INT NOT NULL,
    id_no_origem INT NOT NULL,
    id_no_destino INT NOT NULL,
    distancia_metros DECIMAL(8,2) NOT NULL,
    CONSTRAINT fk_test_mapa_aresta_instituicao
        FOREIGN KEY (id_instituicao) REFERENCES Instituicao(id_instituicao) ON DELETE CASCADE,
    CONSTRAINT fk_test_mapa_aresta_origem
        FOREIGN KEY (id_no_origem) REFERENCES Mapa_No(id_no) ON DELETE CASCADE,
    CONSTRAINT fk_test_mapa_aresta_destino
        FOREIGN KEY (id_no_destino) REFERENCES Mapa_No(id_no) ON DELETE CASCADE,
    UNIQUE KEY unique_mapa_aresta (id_instituicao, id_no_origem, id_no_destino)
);

CREATE TABLE Evento (
    id_evento INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    data DATETIME NOT NULL,
    status VARCHAR(10) NOT NULL,
    id_local INT NOT NULL,
    id_organizacao INT NOT NULL,
    id_organizador INT NOT NULL,
    CONSTRAINT fk_test_evento_local
        FOREIGN KEY (id_local) REFERENCES Locais(id_local) ON DELETE CASCADE,
    CONSTRAINT fk_test_evento_organizacao
        FOREIGN KEY (id_organizacao) REFERENCES Organizacao(id_organizacao) ON DELETE CASCADE,
    CONSTRAINT fk_test_evento_organizador
        FOREIGN KEY (id_organizador) REFERENCES Organizador(id_organizador) ON DELETE CASCADE
);

CREATE TABLE Comentario (
    id_comentario INT PRIMARY KEY AUTO_INCREMENT,
    id_aluno INT NOT NULL,
    id_evento INT NOT NULL,
    comentario VARCHAR(140) NOT NULL,
    CONSTRAINT fk_test_comentario_aluno
        FOREIGN KEY (id_aluno) REFERENCES Aluno(id_aluno) ON DELETE CASCADE,
    CONSTRAINT fk_test_comentario_evento
        FOREIGN KEY (id_evento) REFERENCES Evento(id_evento) ON DELETE CASCADE
);

CREATE TABLE Favorito (
    id_favorito INT PRIMARY KEY AUTO_INCREMENT,
    id_aluno INT NOT NULL,
    id_local INT NOT NULL,
    CONSTRAINT fk_test_favorito_aluno
        FOREIGN KEY (id_aluno) REFERENCES Aluno(id_aluno) ON DELETE CASCADE,
    CONSTRAINT fk_test_favorito_local
        FOREIGN KEY (id_local) REFERENCES Locais(id_local) ON DELETE CASCADE,
    UNIQUE KEY unique_aluno_local (id_aluno, id_local)
);

