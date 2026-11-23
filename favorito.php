<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$user = require_user();
require_post();
verify_csrf();
$id = contact_id($_POST);
$contact = owned_contact($id, $user['id']);
query('UPDATE contactos SET favorito = CASE WHEN favorito = 1 THEN 0 ELSE 1 END WHERE id = ? AND id_usuario = ?',
    [$id, $user['id']]);
$_SESSION['flash'] = (int) $contact['favorito'] === 1
    ? 'Quitaste este contacto de favoritos.'
    : 'Contacto guardado en favoritos.';
redirect('dashboard.php');
