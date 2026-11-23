# Guía corta para subir la Agenda Personal a Hostinger

Esta guía es para subir la aplicación sin rehacer la tarea. Necesitas un plan de Hostinger que permita ejecutar **PHP y MySQL**; un constructor de sitios estáticos por sí solo no sirve para este proyecto.

## 1. Prepara el sitio

Entra a hPanel y crea o selecciona un sitio web PHP/HTML. Usa el dominio, subdominio o dominio temporal que te permita tu plan. El enlace que entregue la plataforma será la futura URL de la agenda.

## 2. Crea la base de datos

En la administración del sitio, busca **Bases de datos → Gestión** o **MySQL**. Crea una base nueva y su usuario. Anota de forma privada:

- Nombre de la base, por ejemplo: `u123456789_agenda`
- Usuario MySQL, por ejemplo: `u123456789_agenda`
- Contraseña MySQL (la que tú configures)
- Host del servidor MySQL (usa el dato que te muestre Hostinger)

No compartas la contraseña por GitHub ni la pongas en capturas.

## 3. Importa el SQL correcto

Entra a **phpMyAdmin**, abre la base que acabas de crear y entra en **Importar**.

- **Instalación nueva / sin contactos previos:** importa `database.sql`.
- **Base anterior con usuarios y contactos que quieres conservar:** haz antes una copia de seguridad e importa solamente `migracion_desde_v1.sql` sobre esa base anterior. Ejecútalo una sola vez.

No ejecutes los dos scripts uno detrás de otro sobre la misma instalación. El SQL de instalación nueva no utiliza `CREATE DATABASE` ni `USE`, porque Hostinger asigna el nombre de la base desde su panel.

## 4. Sube los archivos de la web

Abre el Administrador de archivos y entra en **public_html**. Sube `Agenda_Personal_UTPL_Subir_Public_HTML.zip` y usa **Extraer** ahí mismo.

Al terminar debe quedar así (sin una carpeta adicional entre `public_html` e `index.php`):

```text
public_html/
    index.php
    dashboard.php
    login.php
    registro.php
    crear.php
    editar.php
    favorito.php
    confirmar.php
    eliminar.php
    logout.php
    assets/
    config/
    includes/
    .htaccess
```

Si los archivos quedaron dentro de otra carpeta, muévelos al nivel correcto. Puedes borrar el ZIP del servidor después de extraerlo.

## 5. Configura la conexión MySQL

En el Administrador de archivos, abre `public_html/config/`.

1. Copia `database.local.example.php` y ponle de nombre `database.local.php`.
2. Edita **solo la copia** e ingresa el hostname, el nombre, el usuario y la contraseña de la base de datos que creaste en hPanel.
3. Guarda el archivo. **No lo subas después a GitHub.**

Ejemplo (datos ficticios):

```php
<?php
return [
    'host' => 'localhost',
    'port' => 3306,
    'name' => 'u123456789_agenda',
    'user' => 'u123456789_agenda',
    'password' => 'AQUI_TU_CLAVE_REAL',
];
```

La ruta `config` está protegida contra el acceso directo desde el navegador por `.htaccess`. Para mayor seguridad, no publiques claves en archivos accesibles ni en el repositorio.

## 6. Comprueba la web

Entra a la URL de tu sitio. Prueba en este orden:

1. Registro e inicio de sesión.
2. Crear un contacto.
3. Buscar por nombre, correo y ciudad.
4. Ordenar por nombre, ciudad y fecha.
5. Seleccionar Familia, Amigos, Trabajo, Universidad u Otro.
6. Marcar un favorito, filtrar solo favoritos y quitar la marca.
7. Editar, borrar y cerrar sesión.
8. Repetir el acceso desde otro navegador o teléfono.

Si aparece **No pudimos abrir la agenda**, revisa primero los datos de MySQL, la importación del SQL y que tu plan admita PHP. Si muestra un error 403 o 500 al abrir todo el sitio, revisa la ubicación de los archivos y las reglas de `.htaccess`.

## 7. Completa la entrega

- En `README.md`, cambia el texto pendiente por la **URL pública real**.
- El ZIP ya incluye capturas locales de acceso y registro. Añade una captura real del directorio en funcionamiento como `docs/capturas/directorio.png`.
- Actualiza el repositorio de GitHub con los archivos nuevos, el SQL, el README y las capturas (sin la contraseña MySQL).
- En el aula virtual entrega URL de la web, URL de GitHub y `database.sql`.

**Ojo con la rúbrica:** la consigna indica hosting gratuito. Si tu plan en Hostinger es de pago, el sitio puede funcionar perfectamente, pero conviene confirmar con el docente que el proveedor cumple ese requisito.

**Documentación de Hostinger:**
- https://www.hostinger.com/support/1864324-how-to-upload-and-set-up-your-database-at-hostinger/
- https://www.hostinger.com/support/1884234-how-to-upload-backups-at-hostinger/
- https://www.hostinger.com/support/how-to-upload-a-website-from-backups/
