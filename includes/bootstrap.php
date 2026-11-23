<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/view.php';

set_exception_handler(static function (Throwable $exception): void {
    error_log('Agenda Personal: ' . get_class($exception) . ' [' . $exception->getCode() . ']');
    problem(500, 'No pudimos abrir la agenda',
        'Revisa la conexión con la base de datos e inténtalo de nuevo. Si acabas de subir la página, comprueba los datos de MySQL en Hostinger.');
});

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");

$https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
$cookiePath = rtrim(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/') . '/';
session_name('AgendaPersonalSID');
session_start([
    'use_strict_mode' => 1,
    'use_only_cookies' => 1,
    'cookie_path' => $cookiePath,
    'cookie_httponly' => true,
    'cookie_secure' => $https,
    'cookie_samesite' => 'Lax',
]);

function redirect(string $location): never
{
    header('Location: ' . $location, true, 303);
    exit;
}

function visitor_only(): void
{
    if (isset($_SESSION['user'])) {
        redirect('dashboard.php');
    }
}

function require_user(): array
{
    if (!isset($_SESSION['user'])) {
        redirect('login.php');
    }
    return $_SESSION['user'];
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function require_post(): void
{
    if (!is_post()) {
        header('Allow: POST');
        problem(405, 'Método no permitido', 'Utiliza los formularios de la agenda para hacer cambios.');
    }
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? null;
    if (!is_string($token) || !isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        problem(403, 'La solicitud no es válida', 'Actualiza la página e inténtalo de nuevo.');
    }
}

function string_field(array $input, string $key): string
{
    return is_string($input[$key] ?? null) ? $input[$key] : '';
}

function text_field(string $key): string
{
    return trim(string_field($_POST, $key));
}

function valid_text(string $value): bool
{
    return preg_match('/\A[^\p{C}]{1,100}\z/u', $value) === 1;
}

function valid_email(string $email): bool
{
    return strlen($email) <= 254 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function valid_password(string $password): bool
{
    return strlen($password) <= 72 && !str_contains($password, "\0")
        && preg_match('/\A.{8,}\z/us', $password) === 1;
}

function contact_id(array $input): int
{
    $raw = string_field($input, 'id');
    $id = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
    if ($id === false || (string) $id !== $raw) {
        problem(400, 'Identificador incorrecto', 'Abre el contacto desde tu agenda.');
    }
    return (int) $id;
}

function contact_not_found(): never
{
    problem(404, 'Contacto no disponible', 'Ese contacto no está en tu agenda.');
}

function owned_contact(int $id, int $userId): array
{
    $contact = query(
        'SELECT id, nombre, telefono, correo, ciudad, categoria_id, favorito, fecha_creacion
         FROM contactos WHERE id = ? AND id_usuario = ?',
        [$id, $userId]
    )->fetch();
    return $contact ?: contact_not_found();
}

function categories(): array
{
    return query('SELECT id, nombre FROM categorias ORDER BY id')->fetchAll();
}
