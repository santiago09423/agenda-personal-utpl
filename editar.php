<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/contact_form.php';

$user = require_user();
$id = contact_id($_GET);
if (is_post()) {
    verify_csrf();
}
$values = owned_contact($id, $user['id']);
$categories = categories();
$errors = [];

if (is_post()) {
    $values = contact_values();
    $errors = contact_errors($values, $categories);
    if (!$errors) {
        query('UPDATE contactos SET nombre = ?, telefono = ?, correo = ?, ciudad = ?, categoria_id = ?, favorito = ?
               WHERE id = ? AND id_usuario = ?',
            [$values['nombre'], $values['telefono'], $values['correo'], $values['ciudad'], $values['categoria_id'], $values['favorito'], $id, $user['id']]);
        $_SESSION['flash'] = 'Cambios guardados correctamente.';
        redirect('dashboard.php');
    }
    http_response_code(422);
}
contact_form($user, $values, $errors, $categories, $id);
