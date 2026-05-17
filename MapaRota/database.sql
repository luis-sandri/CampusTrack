
CREATE DATABASE campustrack_mapa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campustrack_mapa;

-- ============================================================
-- TABELAS
-- ============================================================

CREATE TABLE edificios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    descricao TEXT,
    cor VARCHAR(7) DEFAULT '#3B82F6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB;

CREATE TABLE andares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edificio_id INT NOT NULL,
    numero INT NOT NULL DEFAULT 0,
    nome VARCHAR(50) NOT NULL,
    mapa_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE CASCADE,
    UNIQUE KEY uk_edificio_andar (edificio_id, numero),
    INDEX idx_edificio (edificio_id)
) ENGINE=InnoDB;

CREATE TABLE locais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    tipo ENUM('entrada','sala','laboratorio','biblioteca','auditorio','portao','corredor','banheiro','cantina','estacionamento','quadra','ginasio','arena','outro') NOT NULL DEFAULT 'outro',
    x DECIMAL(10,2) NOT NULL,
    y DECIMAL(10,2) NOT NULL,
    andar_id INT,
    edificio_id INT,
    is_navigable BOOLEAN DEFAULT TRUE,
    is_waypoint BOOLEAN DEFAULT FALSE,
    icone VARCHAR(50) DEFAULT 'pin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (andar_id) REFERENCES andares(id) ON DELETE SET NULL,
    FOREIGN KEY (edificio_id) REFERENCES edificios(id) ON DELETE SET NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_edificio (edificio_id),
    INDEX idx_coords (x, y)
) ENGINE=InnoDB;

CREATE TABLE caminhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origem_id INT NOT NULL,
    destino_id INT NOT NULL,
    peso DECIMAL(10,2) NOT NULL,
    tipo ENUM('caminhada','escada','elevador','rampa','externo') DEFAULT 'caminhada',
    bidirecional BOOLEAN DEFAULT TRUE,
    acessivel BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (origem_id) REFERENCES locais(id) ON DELETE CASCADE,
    FOREIGN KEY (destino_id) REFERENCES locais(id) ON DELETE CASCADE,
    UNIQUE KEY uk_caminho (origem_id, destino_id),
    INDEX idx_origem (origem_id),
    INDEX idx_destino (destino_id)
) ENGINE=InnoDB;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    tipo ENUM('aluno','professor','admin','visitante') DEFAULT 'aluno',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB;

CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    local_id INT NOT NULL,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME,
    tipo ENUM('aula','palestra','workshop','reuniao','outro') DEFAULT 'outro',
    criado_por INT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (local_id) REFERENCES locais(id) ON DELETE CASCADE,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_local (local_id),
    INDEX idx_data (data_inicio),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB;

CREATE TABLE historico_rotas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    origem_id INT NOT NULL,
    destino_id INT NOT NULL,
    distancia_total DECIMAL(10,2),
    caminho_json JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (origem_id) REFERENCES locais(id) ON DELETE CASCADE,
    FOREIGN KEY (destino_id) REFERENCES locais(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB;

-- ============================================================
-- EDIFICIOS
-- ============================================================
INSERT INTO edificios (nome, codigo, descricao, cor) VALUES
('Bloco 1',    'B1',  'Bloco 1 - Salas de aula',  '#EF4444'),
('Bloco 2',    'B2',  'Bloco 2 - Salas de aula',  '#F97316'),
('Bloco 3',    'B3',  'Bloco 3 - Salas de aula',  '#EAB308'),
('Bloco 5',    'B5',  'Bloco 5 - Salas de aula',  '#22C55E'),
('Bloco 6',    'B6',  'Bloco 6 - Salas de aula',  '#3B82F6'),
('Bloco 7',    'B7',  'Bloco 7 - Salas de aula',  '#8B5CF6'),
('Bloco 8',    'B8',  'Bloco 8 - Salas de aula',  '#EC4899'),
('Bloco 9',    'B9',  'Bloco 9 - Salas de aula',  '#06B6D4'),
('Bloco 10',   'B10', 'Bloco 10 - Salas de aula', '#F59E0B'),
('Biblioteca', 'BIB', 'Biblioteca Central',        '#6366F1'),
('Ginásio',    'GIN', 'Ginásio Esportivo',         '#10B981'),
('Arena',      'ARE', 'Arena Multiuso',            '#DC2626');

-- ============================================================
-- ANDARES
-- ============================================================
INSERT INTO andares (edificio_id, numero, nome) VALUES
(1,0,'Térreo'),(1,1,'1º Andar'),
(2,0,'Térreo'),(2,1,'1º Andar'),
(3,0,'Térreo'),(3,1,'1º Andar'),
(4,0,'Térreo'),(4,1,'1º Andar'),
(5,0,'Térreo'),
(6,0,'Térreo'),
(7,0,'Térreo'),
(8,0,'Térreo'),
(9,0,'Térreo'),
(10,0,'Térreo'),
(11,0,'Térreo'),
(12,0,'Térreo');

-- ============================================================
-- LOCAIS (blocos visíveis)
-- ============================================================
INSERT INTO locais (id, nome, tipo, x, y, edificio_id, is_navigable, is_waypoint, icone) VALUES
( 1, 'Bloco 1',    'sala',       31.83, 43.00,  1, 1, 0, 'building'),
( 2, 'Bloco 2',    'sala',       32.07, 65.37,  2, 1, 0, 'building'),
( 3, 'Bloco 3',    'sala',       29.14, 85.66,  3, 1, 0, 'building'),
( 4, 'Bloco 5',    'sala',        9.53, 57.60,  4, 1, 0, 'building'),
( 5, 'Bloco 6',    'sala',        7.03, 72.75,  5, 1, 0, 'building'),
( 6, 'Bloco 7',    'sala',       50.48, 29.11,  6, 1, 0, 'building'),
( 7, 'Bloco 8',    'sala',       23.64, 13.52,  7, 1, 0, 'building'),
( 8, 'Bloco 9',    'sala',       13.88, 18.27,  8, 1, 0, 'building'),
( 9, 'Bloco 10',   'sala',        7.66, 23.41,  9, 1, 0, 'building'),
(10, 'Biblioteca', 'biblioteca', 47.41, 81.97, 10, 1, 0, 'library'),
(11, 'Ginásio',    'ginasio',    74.56, 47.99, 11, 1, 0, 'sports'),
(12, 'Arena',      'arena',      63.09, 80.90, 12, 1, 0, 'auditorium');

-- ============================================================
-- WAYPOINTS (invisíveis no mapa, só para roteamento)
-- ============================================================
INSERT INTO locais (id, nome, tipo, x, y, is_navigable, is_waypoint, icone) VALUES
(23, 'Cruzamento Central', 'corredor', 25.00, 55.00, 1, 1, 'waypoint'),
(24, 'Cruzamento Norte',   'corredor', 25.00, 35.00, 1, 1, 'waypoint'),
(25, 'Cruzamento Sul',     'corredor', 38.00, 72.00, 1, 1, 'waypoint'),
(26, 'Cruzamento Leste',   'corredor', 55.00, 65.00, 1, 1, 'waypoint'),
(27, 'Cruzamento Oeste',   'corredor', 10.00, 60.00, 1, 1, 'waypoint'),
(28, 'Via Norte-Oeste',    'corredor', 12.00, 22.00, 1, 1, 'waypoint'),
(29, 'Via Leste',          'corredor', 65.00, 55.00, 1, 1, 'waypoint');

-- ============================================================
-- CAMINHOS
-- ============================================================
INSERT INTO caminhos (origem_id, destino_id, peso, tipo) VALUES
-- Cruzamento Central
(23,  1, 12.0, 'caminhada'),
(23,  2, 10.0, 'caminhada'),
(23,  3, 14.0, 'caminhada'),
(23, 10, 12.0, 'caminhada'),
(23, 12, 10.0, 'caminhada'),
(23, 24, 18.0, 'caminhada'),
(23, 25, 15.0, 'caminhada'),
(23, 26, 22.0, 'caminhada'),
(23, 27, 18.0, 'caminhada'),
-- Cruzamento Norte
(24,  6, 15.0, 'caminhada'),
(24, 28, 12.0, 'caminhada'),
-- Via Norte-Oeste
(28,  7,  8.0, 'caminhada'),
(28,  8,  8.0, 'caminhada'),
(28,  9, 10.0, 'caminhada'),
-- Cruzamento Oeste
(27,  4,  8.0, 'caminhada'),
(27,  5, 10.0, 'caminhada'),
-- Cruzamento Leste
(26, 11, 10.0, 'caminhada'),
(26, 29, 10.0, 'caminhada'),
-- Cruzamento Sul
(25, 10, 10.0, 'caminhada'),
(25, 12,  8.0, 'caminhada');

-- ============================================================
-- USUARIO ADMIN
-- ============================================================
INSERT INTO usuarios (nome, email, senha_hash, tipo) VALUES
('Administrador', 'admin@campustrack.edu', '$2y$10$placeholder_hash_replace_me', 'admin');