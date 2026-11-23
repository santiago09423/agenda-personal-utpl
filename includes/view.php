<?php
declare(strict_types=1);

function e(string|int $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'book' => '<rect x="5" y="3" width="15" height="18" rx="2"/><path d="M3 8h4M3 16h4"/><circle cx="13" cy="9" r="2"/><path d="M9.5 16a3.5 3.5 0 0 1 7 0"/>',
        'users' => '<circle cx="10" cy="8" r="3"/><path d="M3 20v-2a5 5 0 0 1 5-5h4a5 5 0 0 1 5 5v2M17 5a3 3 0 0 1 0 6m1 3a4 4 0 0 1 3 4v2"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="m14 6 4 4M4 20l4-.8L20 7a2.8 2.8 0 0 0-4-4L4.8 15z"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 11v5m4-5v5"/>',
        'exit' => '<path d="M11 5H5v14h6m-1-7h11m-4-4 4 4-4 4"/>',
        'back' => '<path d="m10 6-6 6 6 6M4 12h16"/>',
        'lock' => '<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'star' => '<path d="m12 2.8 2.87 5.8 6.4.93-4.63 4.5 1.09 6.37L12 17.4l-5.73 3.0 1.1-6.37-4.64-4.5 6.41-.93L12 2.8z"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6"/><path d="m15 15 5.3 5.3"/>',
        'filter' => '<path d="M4 5h16l-6.5 7.5V19l-3 2v-8.5z"/>',
    ];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($paths[$name] ?? $paths['book']) . '</svg>';
}

function avatar(string $name): string
{
    preg_match('/^\p{L}/u', $name, $match);
    return e($match[0] ?? '•');
}

function page_start(string $title, ?array $user = null): void
{
    ?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Agenda personal para organizar contactos por nombre, ciudad y categoría.">
        <title><?= e($title) ?> · Agenda Personal</title>
        <link rel="stylesheet" href="assets/css/app.css">
    </head>
    <body>
    <a class="skip" href="#main">Ir al contenido</a>
    <header class="topbar">
        <div class="top-inner container">
            <a class="brand" href="index.php"><span class="brand-icon"><?= icon('book') ?></span> Agenda Personal</a>
            <?php if ($user): ?>
                <form method="post" action="logout.php">
                    <?php csrf_input(); ?>
                    <button class="button button-quiet" type="submit"><?= icon('exit') ?> Cerrar sesión</button>
                </form>
            <?php endif; ?>
        </div>
    </header>
    <main class="container" id="main">
    <?php
}

function page_end(): void
{
    ?>
    </main>
    <footer class="container footer"><span>Agenda Personal</span><span>Trabajo de Desarrollo Web · UTPL</span></footer>
    </body>
    </html>
    <?php
}

function csrf_input(): void
{
    ?><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><?php
}

function field(string $name, string $label, string $value = '', string $type = 'text',
    string $autocomplete = 'off', int $max = 100, string $hint = ''): void
{
    ?>
    <div class="field">
        <label for="<?= e($name) ?>"><?= e($label) ?></label>
        <input id="<?= e($name) ?>" name="<?= e($name) ?>" type="<?= e($type) ?>"
            value="<?= e($value) ?>" maxlength="<?= $max ?>" autocomplete="<?= e($autocomplete) ?>"
            <?= $hint !== '' ? 'aria-describedby="' . e($name) . '-hint"' : '' ?> required>
        <?php if ($hint !== ''): ?><small id="<?= e($name) ?>-hint"><?= e($hint) ?></small><?php endif; ?>
    </div>
    <?php
}

function errors_view(array $errors): void
{
    if (!$errors) {
        return;
    }
    ?>
    <div class="notice error" role="alert">
        <ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    </div>
    <?php
}

function flash_view(): void
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    if ($message !== null): ?>
        <div class="notice success" role="status"><?= icon('check') ?> <?= e($message) ?></div>
    <?php endif;
}

function auth_start(string $title, string $description): void
{
    page_start($title);
    ?>
    <div class="auth-layout">
        <aside class="auth-aside">
            <div class="auth-graphic" aria-hidden="true">
                <div class="book-tile"><?= icon('book') ?></div>
                <div class="lock-tile"><?= icon('lock') ?></div>
            </div>
            <div><h2>Ten tus contactos a mano.</h2>
            <p>Guarda los números, correos y ciudades de las personas con las que te mantienes en contacto.</p></div>
        </aside>
        <section class="auth-content">
            <h1><?= e($title) ?></h1>
            <p class="subtitle"><?= e($description) ?></p>
    <?php
}

function auth_end(): void
{
    ?></section></div><?php
    page_end();
}

function problem(int $status, string $title, string $detail): never
{
    http_response_code($status);
    page_start($title);
    ?>
    <section class="panel narrow problem">
        <h1><?= e($title) ?></h1>
        <p class="subtitle"><?= e($detail) ?></p>
        <a href="index.php" class="button button-primary">Volver a la agenda</a>
    </section>
    <?php
    page_end();
    exit;
}
