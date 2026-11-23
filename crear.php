<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/contact_form.php';

$user = require_user();
$categories = categories();
$values = ['nombre' => '', 'telefono' => '', 'correo' => '', 'ciudad' => '', 'categoria_id' => 5, 'favorito' => 0];
$errors = [];

if (is_post()) {
    verify_csrf();
    $values = contact_values();
    $errors = contact_errors($values, $categories);
    if (!$errors) {
        query('INSERT INTO contactos (id_usuario, nombre, telefono, correo, ciudad, categoria_id, favorito)
               VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$user['id'], $values['nombre'], $values['telefono'], $values['correo'], $values['ciudad'], $values['categoria_id'], $values['favorito']]);
        $_SESSION['flash'] = 'Listo, el contacto ya está en tu agenda.';
        redirect('dashboard.php');
    }
    http_response_code(422);
}
contact_form($user, $values, $errors, $categories);
