# StreamHub 🎬

Plataforma de streaming de canales de televisión y cobertura de eventos deportivos.

## Estructura del Proyecto

```
streamhub/
├── index.php                ← Router principal (todas las páginas pasan aquí)
├── pages/
│   ├── home.php             ← Página de inicio
│   ├── tv.php               ← Página de canales
│   ├── canal.php            ← Reproductor de canal
│   ├── eventos.php          ← Lista de ligas por deporte
│   ├── liga.php             ← Partidos de una liga (acordeón)
│   └── login.php            ← Login / Registro
├── includes/
│   ├── config.php           ← Constantes y helpers (url(), get())
│   ├── db.php               ← Conexión MySQL singleton
│   ├── navbar.php           ← Navbar compartida
│   └── footer.php           ← Footer compartido
├── assets/
│   ├── css/style.css        ← Estilos globales
│   └── js/
│       ├── theme.js         ← Sistema dark/light (compartido)
│       ├── main.js          ← JS de home (partidos + canales)
│       ├── channels.js      ← JS de la página de canales
│       ├── channel.js       ← JS del reproductor
│       ├── eventos.js       ← JS de la página de eventos
│       ├── liga.js          ← JS de la página de liga
│       └── auth.js          ← JS de autenticación
├── data/
│   ├── channels.json        ← Canales de TV
│   ├── matches.json         ← Partidos del slider del home
│   └── soccer.json          ← Partidos de fútbol (demo)
├── api/
│   ├── auth.php             ← API de autenticación
│   └── partidos.php         ← API de partidos (BD o JSON fallback)
└── config/
    ├── streamhub.sql        ← Script SQL base (usuarios)
    └── sports.sql           ← Script SQL para ligas, equipos, canales, partidos
```

## URLs del sistema (sin extensión .php)

| URL | Descripción |
|-----|-------------|
| `?p=home` | Página de inicio |
| `?p=tv` | Todos los canales |
| `?p=canal&id=3` | Reproductor del canal #3 |
| `?p=eventos&type=soccer` | Ligas de fútbol disponibles |
| `?p=eventos&type=basketball` | Ligas de básquet |
| `?p=liga&id=17&type=soccer` | Partidos de la UCL (liga #17) |
| `?p=login` | Login / Registro |

## Instalación

### Requisitos
- PHP 7.4+
- MySQL 5.7+ / MariaDB 10.3+
- Apache/Nginx (XAMPP/WAMP local)

### Pasos

1. Copiar la carpeta `streamhub/` a `htdocs/`

2. Crear la base de datos:
   - Ejecutar `config/streamhub.sql` (usuarios)
   - Ejecutar `config/sports.sql` (deportes)

3. Ajustar `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'streamhub');
   define('BASE_URL', '/streamhub/');
   ```

4. Acceder: `http://localhost/streamhub/`

## Cómo agregar más deportes

1. Agregar la entrada en `$sports` de `includes/navbar.php`:
   ```php
   'volleyball' => ['icon' => 'fa-volleyball-ball', 'label' => 'Voleibol'],
   ```

2. Crear el archivo JSON en `data/volleyball.json` con el mismo esquema.

3. Insertar registros en la tabla `ligas` con `tipo = 'volleyball'`.

## Cómo conectar la BD en producción

En `pages/eventos.php` y `pages/liga.php`, reemplazar la lectura del JSON por:
```php
$conn   = getDBConnection();
// Ver api/partidos.php para el query completo con JOINs
```

También en `api/partidos.php` hay el query SQL completo comentado, listo para descomentar.

## Credenciales de prueba

| Campo | Valor |
|-------|-------|
| Email | admin@streamhub.com |
| Contraseña | admin123 |

## Tecnologías

- **Frontend:** HTML5, CSS3, Bootstrap 5, Vanilla JS
- **Backend:** PHP 8+ con MySQLi
- **Base de datos:** MySQL/MariaDB
- **Fuentes:** Space Mono + DM Sans
- **Íconos:** Font Awesome 6
