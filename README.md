# Mesa de ayuda UISIL

Aplicación web de gestión de incidencias tecnológicas realizada con PHP, MySQL, HTML, CSS y JavaScript para la confirmación de eliminación. Permite registrar, consultar, buscar, filtrar, editar y eliminar incidencias. La edición, eliminación y actualización de estado requieren una sesión administrativa.

## Requisitos

- XAMPP con Apache, MySQL y PHP 8.1 o superior.
- Extensiones PHP `mysqli` y `mbstring` activas.

## Instalación en XAMPP

1. Inicie **Apache** y **MySQL** desde el panel de XAMPP.
2. Copie esta carpeta dentro de `C:\xampp\htdocs\` (por ejemplo, `C:\xampp\htdocs\mesa-ayuda-uisil`).
3. Abra `http://localhost/phpmyadmin`, elija **Importar** y seleccione `schema.sql`. Este archivo crea la base de datos `incidencias_uisil` y las tablas. Si ya se importó, omita este paso.
4. Configure `DB_HOST`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` y `DB_PORT` como variables de entorno del servidor o copie `config.local.example.php` a `config.local.php` y complete los valores. Este último archivo contiene credenciales locales y está excluido de Git.
5. Abra `http://localhost/mesa-ayuda-uisil/setup.php` y cree el primer administrador. La página de configuración deja de estar disponible después de crear el usuario.
6. Abra `http://localhost/mesa-ayuda-uisil/index.php` para usar el sistema.

## Funciones principales

- **Público:** registrar incidencias, consultar detalles y filtrar por laboratorio, estado, prioridad o texto.
- **Administrador:** iniciar sesión, editar incidencias, cambiar estados y eliminar con confirmación.
- **Datos:** cada incidencia guarda nombre de quien reporta, laboratorio, equipo, descripción, prioridad, estado y fechas de registro/actualización.

## Demostración breve

1. Registre una incidencia desde **Reportar falla**.
2. Compruebe que aparece en el listado con fecha y estado **Pendiente**.
3. Pruebe los filtros y abra su detalle.
4. Inicie sesión como administrador, cambie su estado y edite un dato.
5. Muestre la confirmación de eliminación y, si desea, elimínela.

## Guion para explicar el código (3 minutos)

1. **Inicio y base de datos:** «`schema.sql` crea dos tablas: `usuarios` para administradores e `incidencias` para los reportes. `config.php` abre la conexión `mysqli`». Muestre el listado en `index.php` y sus contadores.
2. **Registro y consulta:** «`create.php` valida y guarda una incidencia con una consulta preparada. `index.php` permite buscar y filtrar; `view.php` enseña el detalle y las fechas». Registre una falla y filtre el resultado.
3. **Administración:** «`setup.php` crea la primera cuenta y guarda un hash de la contraseña mediante `password_hash`; `login.php` la verifica y guarda la sesión. Solo una sesión administrativa puede editar, cambiar el estado o eliminar». Inicie sesión y cambie el estado.
4. **Seguridad y diseño:** «`functions.php` reúne la validación, los tokens CSRF y el escape HTML. `assets/styles.css` define la apariencia oscura y adaptable». Señale la confirmación antes de eliminar.

Los comentarios `//` en PHP, `<!-- -->` en HTML, `--` en SQL y `/* */` en CSS marcan los bloques principales para ubicar cada parte durante la exposición.

## Estructura

| Archivo | Función |
| --- | --- |
| `schema.sql` | Base de datos y tablas |
| `config.php`, `functions.php` | Conexión, validación, sesiones y funciones comunes |
| `config.local.php` | Credenciales locales (no se publica) |
| `index.php`, `view.php`, `create.php` | Listado, detalle y registro |
| `edit.php`, `status.php`, `delete.php` | Administración de incidencias |
| `setup.php`, `login.php`, `logout.php` | Cuenta inicial y autenticación |
| `assets/styles.css` | Diseño adaptable |

La contraseña se almacena mediante `password_hash`; las consultas que reciben datos del usuario usan sentencias preparadas. Los formularios incluyen protección CSRF y las salidas HTML se escapan.
