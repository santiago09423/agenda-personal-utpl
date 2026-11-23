<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/contact_form.php';

$user = require_user();
$id = contact_id($_GET);
$contact = owned_contact($id, $user['id']);
page_start('Eliminar contacto', $user);
?>
<div class="narrow">
    <?php back_link(); ?>
    <section class="panel form-panel">
        <span class="empty-icon delete-icon"><?= icon('trash') ?></span>
        <h1>Eliminar contacto</h1>
        <p class="subtitle">¿Seguro que quieres eliminar a <strong><?= e($contact['nombre']) ?></strong>? No podrás recuperar sus datos desde la agenda.</p>
        <form method="post" action="eliminar.php" class="form-actions">
            <?php csrf_input(); ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <a href="dashboard.php" class="button button-quiet">Cancelar</a>
            <button type="submit" class="button button-danger">Sí, eliminar</button>
        </form>
    </section>
</div>
<?php page_end();
