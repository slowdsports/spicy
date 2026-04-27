-- ============================================================
-- StreamHub - Tablas adicionales para el módulo de deportes
-- Ejecutar DESPUÉS de streamhub.sql
-- ============================================================



-- ============================================================
-- TABLA: ligas
-- ============================================================
CREATE TABLE IF NOT EXISTS ligas (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(150) NOT NULL,
    logo       VARCHAR(500) DEFAULT NULL,
    pais       VARCHAR(100) DEFAULT NULL,
    tipo       VARCHAR(60)  NOT NULL DEFAULT 'soccer', -- football, basketball, etc.
    activo     TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: equipos
-- ============================================================
CREATE TABLE IF NOT EXISTS equipos (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(150) NOT NULL,
    logo       VARCHAR(500) DEFAULT NULL,
    pais       VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: canales (versión BD de channels.json)
-- ============================================================
CREATE TABLE IF NOT EXISTS canales (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150) NOT NULL,
    logo        VARCHAR(500) DEFAULT NULL,
    stream_url  VARCHAR(1000) DEFAULT NULL,
    category    VARCHAR(100) DEFAULT NULL,
    views       VARCHAR(20)  DEFAULT '0',
    activo      TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: partidos
-- (igual que el CREATE proporcionado, con FK a las nuevas tablas)
-- ============================================================
CREATE TABLE IF NOT EXISTS partidos (
    id          INT NOT NULL PRIMARY KEY,
    local       INT NOT NULL,
    visitante   INT DEFAULT NULL,
    liga        INT NOT NULL,
    fecha_hora  DATETIME DEFAULT NULL,
    tipo        VARCHAR(60) COLLATE utf8mb4_general_ci DEFAULT NULL,
    starp       INT DEFAULT NULL,
    vix         INT DEFAULT NULL,
    canal1      INT DEFAULT NULL,
    canal2      INT DEFAULT NULL,
    canal3      INT DEFAULT NULL,
    canal4      INT DEFAULT NULL,
    canal5      INT DEFAULT NULL,
    canal6      INT DEFAULT NULL,
    canal7      INT DEFAULT NULL,
    canal8      INT DEFAULT NULL,
    canal9      INT DEFAULT NULL,
    canal10     INT DEFAULT NULL,
    FOREIGN KEY (local)     REFERENCES equipos(id) ON DELETE RESTRICT,
    FOREIGN KEY (visitante) REFERENCES equipos(id) ON DELETE SET NULL,
    FOREIGN KEY (liga)      REFERENCES ligas(id)   ON DELETE RESTRICT,
    FOREIGN KEY (canal1)    REFERENCES canales(id) ON DELETE SET NULL,
    FOREIGN KEY (canal2)    REFERENCES canales(id) ON DELETE SET NULL,
    FOREIGN KEY (canal3)    REFERENCES canales(id) ON DELETE SET NULL,
    FOREIGN KEY (canal4)    REFERENCES canales(id) ON DELETE SET NULL,
    FOREIGN KEY (canal5)    REFERENCES canales(id) ON DELETE SET NULL,
    FOREIGN KEY (canal6)    REFERENCES canales(id) ON DELETE SET NULL,
    FOREIGN KEY (canal7)    REFERENCES canales(id) ON DELETE SET NULL,
    FOREIGN KEY (canal8)    REFERENCES canales(id) ON DELETE SET NULL,
    FOREIGN KEY (canal9)    REFERENCES canales(id) ON DELETE SET NULL,
    FOREIGN KEY (canal10)   REFERENCES canales(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- DATOS DE EJEMPLO
-- ============================================================

-- Ligas
INSERT IGNORE INTO ligas (id, nombre, logo, pais, tipo) VALUES
(5,  'La Liga',               'https://upload.wikimedia.org/wikipedia/commons/5/54/LaLiga_EA_Sports_Logo_2023.svg', 'España',    'soccer'),
(8,  'Premier League',        'https://upload.wikimedia.org/wikipedia/en/f/f2/Premier_League_Logo.svg',            'Inglaterra', 'soccer'),
(17, 'UEFA Champions League', 'https://upload.wikimedia.org/wikipedia/en/b/bf/UEFA_Champions_League_logo_2.svg',   'Europa',     'soccer');

-- Equipos
INSERT IGNORE INTO equipos (id, nombre, logo) VALUES
(35,  'Bayern München',     'https://upload.wikimedia.org/wikipedia/commons/1/1b/FC_Bayern_M%C3%BCnchen_logo_%282017%29.svg'),
(60,  'Real Madrid',        'https://upload.wikimedia.org/wikipedia/en/5/56/Real_Madrid_CF.svg'),
(70,  'Arsenal',            'https://upload.wikimedia.org/wikipedia/en/5/53/Arsenal_FC.svg'),
(71,  'Paris Saint-Germain','https://upload.wikimedia.org/wikipedia/en/a/a7/Paris_Saint-Germain_F.C..svg'),
(80,  'Manchester City',    'https://upload.wikimedia.org/wikipedia/en/e/eb/Manchester_City_FC_badge.svg'),
(81,  'Liverpool',          'https://upload.wikimedia.org/wikipedia/en/0/0c/Liverpool_FC.svg'),
(90,  'Chelsea',            'https://upload.wikimedia.org/wikipedia/en/c/cc/Chelsea_FC.svg'),
(91,  'Tottenham',          'https://upload.wikimedia.org/wikipedia/en/b/b4/Tottenham_Hotspur.svg'),
(100, 'FC Barcelona',       'https://upload.wikimedia.org/wikipedia/en/4/47/FC_Barcelona_%28crest%29.svg'),
(101, 'Atlético Madrid',    'https://upload.wikimedia.org/wikipedia/en/f/f4/Atletico_Madrid_2017_logo.svg');

-- Canales
INSERT IGNORE INTO canales (id, nombre, logo, stream_url, category, views) VALUES
(914, 'ESPN',       'https://upload.wikimedia.org/wikipedia/commons/2/2f/ESPN_wordmark.svg',                                                           'https://www.youtube.com/embed/live_stream?channel=UCiWLfSweyRNmLpgEHekhoAg', 'Deportes', '12.4K'),
(915, 'Fox Sports', 'https://upload.wikimedia.org/wikipedia/commons/7/74/Fox_Sports_2019.svg',                                                         'https://www.youtube.com/embed/live_stream?channel=UCiWLfSweyRNmLpgEHekhoAg', 'Deportes', '8.7K'),
(916, 'Sky Sports', 'https://upload.wikimedia.org/wikipedia/en/thumb/a/a6/Sky_Sports_logo_2020.svg/1200px-Sky_Sports_logo_2020.svg.png',               'https://www.youtube.com/embed/live_stream?channel=UCiWLfSweyRNmLpgEHekhoAg', 'Deportes', '7.8K'),
(920, 'TUDN',       'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3d/TUDN_logo.svg/2560px-TUDN_logo.svg.png',                               'https://www.youtube.com/embed/live_stream?channel=UCiWLfSweyRNmLpgEHekhoAg', 'Deportes', '9.3K');

-- Partidos
INSERT IGNORE INTO partidos (id, local, visitante, liga, fecha_hora, tipo, canal2, canal4) VALUES
(12436535, 60, 35, 17, '2025-04-27 15:00:00', 'soccer', 914, 915);

INSERT IGNORE INTO partidos (id, local, visitante, liga, fecha_hora, tipo, canal1) VALUES
(12436540, 70, 71, 17, '2025-04-28 20:45:00', 'soccer', 920);

INSERT IGNORE INTO partidos (id, local, visitante, liga, fecha_hora, tipo, canal1, canal2) VALUES
(12436550, 80, 81, 8, '2025-04-26 18:00:00', 'soccer', 914, 916),
(12436560, 90, 91, 8, '2025-04-29 20:00:00', 'soccer', 914, NULL);

INSERT IGNORE INTO partidos (id, local, visitante, liga, fecha_hora, tipo, canal1, canal2, canal3) VALUES
(12436570, 100, 101, 5, '2025-04-27 21:00:00', 'soccer', 915, 920, 914);

SELECT 'Tablas de deportes creadas exitosamente' AS estado;
