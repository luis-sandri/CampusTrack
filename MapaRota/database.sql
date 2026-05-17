-- ============================================================
-- CampusTrack Database Schema v2.0
-- Normalized, indexed, with proper foreign keys
-- ============================================================

DROP DATABASE IF EXISTS campustrack;
CREATE DATABASE campustrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campustrack;

-- ============================================================
-- 1. EDIFICIOS (Buildings)
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

-- ============================================================
-- 2. ANDARES (Floors)
-- ============================================================
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

-- ============================================================
-- 3. LOCAIS (Locations / Nodes)
-- ============================================================
CREATE TABLE locais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    tipo ENUM('entrada', 'sala', 'laboratorio', 'biblioteca', 'auditorio', 'portao', 'corredor', 'banheiro', 'cantina', 'estacionamento', 'quadra', 'ginasio', 'arena', 'outro') NOT NULL DEFAULT 'outro',
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

-- ============================================================
-- 4. CAMINHOS (Paths / Edges)
-- ============================================================
CREATE TABLE caminhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origem_id INT NOT NULL,
    destino_id INT NOT NULL,
    peso DECIMAL(10,2) NOT NULL,
    tipo ENUM('caminhada', 'escada', 'elevador', 'rampa', 'externo') DEFAULT 'caminhada',
    bidirecional BOOLEAN DEFAULT TRUE,
    acessivel BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (origem_id) REFERENCES locais(id) ON DELETE CASCADE,
    FOREIGN KEY (destino_id) REFERENCES locais(id) ON DELETE CASCADE,
    UNIQUE KEY uk_caminho (origem_id, destino_id),
    INDEX idx_origem (origem_id),
    INDEX idx_destino (destino_id)
) ENGINE=InnoDB;

-- ============================================================
-- 5. USUARIOS (Users)
-- ============================================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    tipo ENUM('aluno', 'professor', 'admin', 'visitante') DEFAULT 'aluno',
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB;

-- ============================================================
-- 6. EVENTOS (Events)
-- ============================================================
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT,
    local_id INT NOT NULL,
    data_inicio DATETIME NOT NULL,
    data_fim DATETIME,
    tipo ENUM('aula', 'palestra', 'workshop', 'reuniao', 'outro') DEFAULT 'outro',
    criado_por INT,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (local_id) REFERENCES locais(id) ON DELETE CASCADE,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_local (local_id),
    INDEX idx_data (data_inicio),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB;

-- ============================================================
-- 7. HISTORICO_ROTAS (Route History)
-- ============================================================
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
-- SEED DATA
-- ============================================================

-- Buildings
-- IDs: 1=Bloco1, 2=Bloco2, 3=Bloco3, 4=Bloco5, 5=Bloco6, 6=Bloco7, 7=Bloco8(B), 8=Bloco9, 9=Bloco10, 10=Biblioteca, 11=Ginasio, 12=Arena
INSERT INTO edificios (nome, codigo, descricao, cor) VALUES
('Bloco 1', 'B1', 'Bloco 1 - Salas de aula', '#EF4444'),
('Bloco 2', 'B2', 'Bloco 2 - Salas de aula', '#F97316'),
('Bloco 3', 'B3', 'Bloco 3 - Salas de aula', '#EAB308'),
('Bloco 5', 'B5', 'Bloco 5 - Salas de aula', '#22C55E'),
('Bloco 6', 'B6', 'Bloco 6 - Salas de aula', '#3B82F6'),
('Bloco 7', 'B7', 'Bloco 7 - Salas de aula', '#8B5CF6'),
('Bloco B', 'BB', 'Bloco B - Salas de aula', '#EC4899'),
('Bloco 9', 'B9', 'Bloco 9 - Salas de aula', '#06B6D4'),
('Bloco 10', 'B10', 'Bloco 10 - Salas de aula', '#F59E0B'),
('Biblioteca', 'BIB', 'Biblioteca Central', '#6366F1'),
('Ginásio', 'GIN', 'Ginásio Esportivo', '#10B981'),
('Arena', 'ARE', 'Arena Multiuso', '#DC2626');

-- Floors (Térreo for each building)
INSERT INTO andares (edificio_id, numero, nome) VALUES
(1, 0, 'Térreo'), (1, 1, '1º Andar'),
(2, 0, 'Térreo'), (2, 1, '1º Andar'),
(3, 0, 'Térreo'), (3, 1, '1º Andar'),
(4, 0, 'Térreo'), (4, 1, '1º Andar'),
(5, 0, 'Térreo'),
(6, 0, 'Térreo'),
(7, 0, 'Térreo'),
(8, 0, 'Térreo'),
(9, 0, 'Térreo'),
(10, 0, 'Térreo'),
(11, 0, 'Térreo'),
(12, 0, 'Térreo');

-- ============================================================
-- LOCAIS (nodes on the map - x,y as % of image 2137x1084)
-- Positions estimated from the annotated aerial photo
-- ============================================================
INSERT INTO locais (nome, descricao, tipo, x, y, edificio_id, is_navigable, icone) VALUES
-- === BLOCOS NUMERADOS (pontos vermelhos da imagem) ===
-- Bloco 1: centro-inferior esquerdo (~col 330, row 610 => 15.5%, 56.3%)
('Bloco 1', 'Bloco 1 - Salas de aula', 'sala', 15.50, 56.30, 1, TRUE, 'building'),

-- Bloco 2: central-esquerdo (~col 390, row 480 => 18.3%, 44.3%)
('Bloco 2', 'Bloco 2 - Salas de aula', 'sala', 34.00, 46.50, 2, TRUE, 'building'),

-- Bloco 3: grande bloco central (~col 440, row 330 => 20.6%, 30.4%)
('Bloco 3', 'Bloco 3 - Salas de aula', 'sala', 22.50, 31.00, 3, TRUE, 'building'),

-- Bloco 5: esquerdo (~col 155, row 410 => 7.3%, 37.8%)
('Bloco 5', 'Bloco 5 - Salas de aula', 'sala', 7.30, 37.80, 4, TRUE, 'building'),

-- Bloco 6: extremo esquerdo inferior (~col 85, row 510 => 4.0%, 47.0%)
('Bloco 6', 'Bloco 6 - Salas de aula', 'sala', 4.00, 47.00, 5, TRUE, 'building'),

-- Bloco 7: superior centro-direito (~col 870, row 195 => 40.7%, 18.0%)
('Bloco 7', 'Bloco 7 - Salas de aula', 'sala', 40.70, 18.00, 6, TRUE, 'building'),

-- Bloco B: superior esquerdo (~col 340, row 100 => 15.9%, 9.2%)
('Bloco B', 'Bloco B - Salas de aula', 'sala', 15.90, 9.20, 7, TRUE, 'building'),

-- Bloco 9: superior esquerdo abaixo de B (~col 205, row 120 => 9.6%, 11.1%)
('Bloco 9', 'Bloco 9 - Salas de aula', 'sala', 9.60, 11.10, 8, TRUE, 'building'),

-- Bloco 10: extremo superior esquerdo (~col 95, row 155 => 4.4%, 14.3%)
('Bloco 10', 'Bloco 10 - Salas de aula', 'sala', 4.40, 14.30, 9, TRUE, 'building'),

-- Biblioteca: centro do mapa (~col 660, row 580 => 30.9%, 53.5%)
('Biblioteca', 'Biblioteca Central', 'biblioteca', 32.00, 56.00, 10, TRUE, 'library'),

-- Ginásio: direita centro (~col 1030, row 490 => 48.2%, 45.2%)
('Ginásio', 'Ginásio Esportivo', 'ginasio', 50.50, 46.30, 11, TRUE, 'sports'),

-- Arena: centro-direito abaixo (~col 915, row 590 => 42.8%, 54.4%)
('Arena', 'Arena Multiuso', 'arena', 44.00, 57.00, 12, TRUE, 'auditorium'),

-- ============================================================
-- WAYPOINTS (interseções para roteamento)
-- ============================================================
('Cruzamento Central', 'Interseção principal', 'corredor', 28.00, 50.00, NULL, TRUE, 'waypoint'),
('Cruzamento Norte', 'Interseção norte', 'corredor', 28.00, 30.00, NULL, TRUE, 'waypoint'),
('Cruzamento Sul', 'Interseção sul', 'corredor', 38.00, 62.00, NULL, TRUE, 'waypoint'),
('Cruzamento Leste', 'Interseção leste', 'corredor', 48.00, 52.00, NULL, TRUE, 'waypoint'),
('Cruzamento Oeste', 'Interseção oeste', 'corredor', 10.00, 45.00, NULL, TRUE, 'waypoint'),
('Via Norte-Oeste', 'Caminho noroeste', 'corredor', 8.00, 20.00, NULL, TRUE, 'waypoint'),
('Via Leste', 'Caminho leste', 'corredor', 58.00, 50.00, NULL, TRUE, 'waypoint'),

-- Portões
('Portão Principal', 'Entrada principal do campus', 'portao', 55.00, 90.00, NULL, TRUE, 'gate'),
('Portão Norte', 'Portão norte', 'portao', 9.00, 5.00, NULL, TRUE, 'gate'),
('Portão Leste', 'Portão lateral direito', 'portao', 93.00, 53.00, NULL, TRUE, 'gate');

-- ============================================================
-- CAMINHOS (edges)
-- IDs dos locais:
--  1=Bloco1, 2=Bloco2, 3=Bloco3, 4=Bloco5, 5=Bloco6, 6=Bloco7
--  7=BlocoB, 8=Bloco9, 9=Bloco10, 10=Biblioteca, 11=Ginasio, 12=Arena
--  13=CruzCentral, 14=CruzNorte, 15=CruzSul, 16=CruzLeste
--  17=CruzOeste, 18=ViaNorteOeste, 19=ViaLeste
--  20=PortPrinc, 21=PortNorte, 22=PortLeste
-- ============================================================
INSERT INTO caminhos (origem_id, destino_id, peso, tipo) VALUES
-- Cruzamento Central conecta blocos centrais
(13, 2,  12.0, 'caminhada'),  -- CruzCentral → Bloco2
(13, 3,  14.0, 'caminhada'),  -- CruzCentral → Bloco3
(13, 10, 12.0, 'caminhada'),  -- CruzCentral → Biblioteca
(13, 12, 10.0, 'caminhada'),  -- CruzCentral → Arena
(13, 14, 20.0, 'caminhada'),  -- CruzCentral → CruzNorte
(13, 15, 15.0, 'caminhada'),  -- CruzCentral → CruzSul
(13, 16, 22.0, 'caminhada'),  -- CruzCentral → CruzLeste
(13, 17, 18.0, 'caminhada'),  -- CruzCentral → CruzOeste

-- CruzNorte liga blocos norte
(14, 3,  10.0, 'caminhada'),  -- CruzNorte → Bloco3
(14, 6,  18.0, 'caminhada'),  -- CruzNorte → Bloco7
(14, 18, 15.0, 'caminhada'),  -- CruzNorte → ViaNorteOeste

-- ViaNorteOeste liga extremo noroeste
(18, 7,  10.0, 'caminhada'),  -- ViaNorteOeste → BlocoB
(18, 8,  8.0,  'caminhada'),  -- ViaNorteOeste → Bloco9
(18, 9,  12.0, 'caminhada'),  -- ViaNorteOeste → Bloco10
(18, 21, 10.0, 'caminhada'),  -- ViaNorteOeste → PortNorte

-- CruzOeste liga blocos do lado esquerdo
(17, 1,  12.0, 'caminhada'),  -- CruzOeste → Bloco1
(17, 4,  10.0, 'caminhada'),  -- CruzOeste → Bloco5
(17, 5,  8.0,  'caminhada'),  -- CruzOeste → Bloco6

-- CruzSul / CruzLeste ligam área leste
(15, 10, 10.0, 'caminhada'),  -- CruzSul → Biblioteca
(15, 12, 8.0,  'caminhada'),  -- CruzSul → Arena
(15, 20, 20.0, 'caminhada'),  -- CruzSul → PortPrincipal

(16, 11, 10.0, 'caminhada'),  -- CruzLeste → Ginasio
(16, 19, 12.0, 'caminhada'),  -- CruzLeste → ViaLeste

(19, 22, 12.0, 'caminhada');  -- ViaLeste → PortLeste

-- ============================================================
-- EVENTOS
-- ============================================================
INSERT INTO eventos (titulo, descricao, local_id, data_inicio, data_fim, tipo) VALUES
('Aula de Programação', 'Introdução a algoritmos', 2, '2026-05-16 08:00:00', '2026-05-16 10:00:00', 'aula'),
('Palestra: IA na Educação', 'Palestra sobre inteligência artificial', 12, '2026-05-16 14:00:00', '2026-05-16 16:00:00', 'palestra'),
('Workshop de Robótica', 'Oficina prática com Arduino', 2, '2026-05-17 09:00:00', '2026-05-17 12:00:00', 'workshop'),
('Reunião Acadêmica', 'Reunião do conselho', 10, '2026-05-16 10:00:00', '2026-05-16 11:30:00', 'reuniao');

-- Admin user
INSERT INTO usuarios (nome, email, senha_hash, tipo) VALUES
('Administrador', 'admin@campustrack.edu', '$2y$10$placeholder_hash_replace_me', 'admin');
