<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$user = require_user();
require_post();
verify_csrf();
$id = contact_id($_POST);
$deleted = query('DELETE FROM contactos WHERE id = ? AND id_usuario = ?', [$id, $user['id']]);
if ($deleted->rowCount() === 0) {
    contact_not_found();
}
$_SESSION['flash'] = 'Contacto eliminado de tu agenda.';
redirect('dashboard.php');
