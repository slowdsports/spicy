-- ============================================================
-- StreamHub - Schema completo actualizado
-- Ejecutar en orden: primero este archivo
-- ============================================================


-- ============================================================
-- TABLA: categorias_canal
-- Categorías para clasificar los canales (Deportes, Noticias, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS categorias_canal (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categorias_canal (nombre) VALUES
('Deportes'), ('Noticias'), ('Entretenimiento'), ('Música'), ('Infantil'), ('Adultos'), ('Películas');

-- ============================================================
-- TABLA: paises
-- Países para clasificar fuentes
-- ============================================================
CREATE TABLE IF NOT EXISTS paises (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    paisCodigo  VARCHAR(60)  NOT NULL UNIQUE,   -- slug de sofascore / código iso
    paisNombre  VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO paises (paisCodigo, paisNombre) VALUES
('international', 'Internacional'),
('es', 'España'),
('us', 'Estados Unidos'),
('mx', 'México'),
('hn', 'Honduras'),
('ar', 'Argentina');

-- ============================================================
-- TABLA: tipos_fuente
-- Tipos de fuente: m3u8, dash, dash-drm, hls, etc.
-- ============================================================
CREATE TABLE IF NOT EXISTS tipos_fuente (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(60) NOT NULL UNIQUE,
    icono   VARCHAR(60) DEFAULT 'fa-play'   -- icono FontAwesome
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO tipos_fuente (nombre, icono) VALUES
('m3u8',     'fa-play'),
('hls',      'fa-play'),
('dash',     'fa-broadcast-tower'),
('dash-drm', 'fa-lock'),
('iframe',   'fa-window-maximize'),
('youtube',  'fa-youtube');

-- ============================================================
-- TABLA: canales (nueva estructura)
-- Un canal es la entidad lógica (ESPN, BBC, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS canales (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150) NOT NULL,
    imagen      VARCHAR(500) DEFAULT NULL,       -- URL o ruta del logo
    categoria   INT NOT NULL,                    -- FK → categorias_canal
    activo      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria) REFERENCES categorias_canal(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: fuentes
-- Cada canal puede tener N fuentes (por país, calidad, tipo, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS fuentes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150) NOT NULL,           -- Ej: "ESPN HD - Latinoamérica"
    canal       INT NOT NULL,                    -- FK → canales
    url         TEXT NOT NULL,                   -- URL del stream
    ck_key      VARCHAR(500) DEFAULT NULL,       -- Clave DRM (Dash DRM)
    ck_keyid    VARCHAR(500) DEFAULT NULL,       -- Key ID DRM
    pais        INT DEFAULT NULL,               -- FK → paises
    tipo        INT NOT NULL,                   -- FK → tipos_fuente
    epg         VARCHAR(500) DEFAULT NULL,       -- EPG ID o URL
    activo      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (canal) REFERENCES canales(id) ON DELETE CASCADE,
    FOREIGN KEY (pais)  REFERENCES paises(id)  ON DELETE SET NULL,
    FOREIGN KEY (tipo)  REFERENCES tipos_fuente(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: ligas (ajustada para sofascore)
-- ============================================================
CREATE TABLE IF NOT EXISTS ligas (
    id          INT NOT NULL PRIMARY KEY,        -- ID de sofascore
    ligaNombre  VARCHAR(200) NOT NULL,
    ligaImg     VARCHAR(500) DEFAULT NULL,       -- slug para imagen local
    ligaPais    VARCHAR(100) DEFAULT NULL,
    tipo        VARCHAR(60)  DEFAULT 'soccer',   -- football, basketball, etc.
    season      VARCHAR(20)  DEFAULT NULL,
    activo      TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: equipos (ajustada para sofascore)
-- ============================================================
CREATE TABLE IF NOT EXISTS equipos (
    id          INT NOT NULL PRIMARY KEY,        -- ID de sofascore
    equipoNombre VARCHAR(200) NOT NULL,
    equipoImg   VARCHAR(500) DEFAULT NULL,
    equipoLiga  INT DEFAULT NULL,
    FOREIGN KEY (equipoLiga) REFERENCES ligas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: partidos
-- ============================================================
CREATE TABLE IF NOT EXISTS partidos (
    id          INT NOT NULL PRIMARY KEY,        -- ID de sofascore
    local       INT NOT NULL,
    visitante   INT DEFAULT NULL,
    liga        INT NOT NULL,
    fecha_hora  DATETIME DEFAULT NULL,
    tipo        VARCHAR(60) DEFAULT NULL,        -- football, tennis, etc.
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
-- TABLA: config_sitio
-- Configuración general del sitio web
-- ============================================================
CREATE TABLE IF NOT EXISTS config_sitio (
    clave       VARCHAR(60)  NOT NULL PRIMARY KEY,
    valor       TEXT         DEFAULT NULL,
    descripcion VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO config_sitio (clave, valor, descripcion) VALUES
('sitio_nombre',       'StreamHub',              'Nombre del sitio'),
('sitio_descripcion',  'TV en Vivo & Deportes',  'Descripción del sitio'),
('sitio_logo',         '',                        'URL del logo principal'),
('mantenimiento',      '0',                       '1 = activar modo mantenimiento'),
('registro_abierto',   '1',                       '1 = permite nuevos registros'),
('timezone',           'America/Tegucigalpa',     'Zona horaria del servidor'),
('color_acento',       '#8b5cf6',                 'Color principal de la interfaz'),
('max_fuentes_canal',  '10',                      'Máximo de fuentes por canal'),
('sofascore_timezone', 'America/Tegucigalpa',     'Timezone para timestamps de sofascore');

SELECT 'Schema StreamHub creado exitosamente' AS estado;
