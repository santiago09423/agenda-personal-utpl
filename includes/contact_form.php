<?php
declare(strict_types=1);

function contact_values(): array
{
    $rawCategory = string_field($_POST, 'categoria_id');
    $category = filter_var($rawCategory, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return [
        'nombre' => text_field('nombre'),
        'telefono' => text_field('telefono'),
        'correo' => text_field('correo'),
        'ciudad' => text_field('ciudad'),
        'categoria_id' => $category === false ? 0 : (int) $category,
        'favorito' => string_field($_POST, 'favorito') === '1' ? 1 : 0,
    ];
}

function contact_errors(array $values, array $categories): array
{
    $errors = [];
    if (!valid_text($values['nombre'])) {
        $errors[] = 'Escribe el nombre del contacto (máximo 100 caracteres).';
    }
    $digits = preg_match_all('/[0-9]/', $values['telefono']);
    if ($digits < 7 || $digits > 15 || preg_match('/\A\+?[0-9() .-]{7,30}\z/', $values['telefono']) !== 1
        || strlen($values['telefono']) > 30) {
        $errors[] = 'El teléfono debe tener entre 7 y 15 dígitos.';
    }
    if (!valid_email($values['correo'])) {
        $errors[] = 'Revisa el correo electrónico del contacto.';
    }
    if (!valid_text($values['ciudad'])) {
        $errors[] = 'Escribe una ciudad válida (máximo 100 caracteres).';
    }
    $validIds = array_map(static fn(array $row): int => (int) $row['id'], $categories);
    if (!in_array((int) $values['categoria_id'], $validIds, true)) {
        $errors[] = 'Selecciona una categoría de la lista.';
    }
    if (isset($_POST['favorito']) && string_field($_POST, 'favorito') !== '1') {
        $errors[] = 'El valor de favorito no es válido.';
    }
    return $errors;
}

function back_link(): void
{
    ?><a href="dashboard.php" class="back"><?= icon('back') ?> Volver a mis contactos</a><?php
}

function contact_form(array $user, array $values, array $errors, array $categories, ?int $id = null): void
{
    $title = $id === null ? 'Nuevo contacto' : 'Editar contacto';
    $action = $id === null ? 'crear.php' : 'editar.php?id=' . $id;
    page_start($title, $user);
    ?>
    <div class="narrow">
        <?php back_link(); ?>
        <section class="panel form-panel">
            <h1><?= e($title) ?></h1>
            <p class="subtitle">Completa sus datos y escoge cómo quieres organizarlo.</p>
            <?php errors_view($errors); ?>
            <form method="post" action="<?= e($action) ?>" class="stack">
                <?php
                csrf_input();
                field('nombre', 'Nombre completo', $values['nombre']);
                field('telefono', 'Teléfono', $values['telefono'], 'tel', 'off', 30, 'Ejemplo: +593 99 123 4567');
                field('correo', 'Correo electrónico', $values['correo'], 'email', 'off', 254);
                field('ciudad', 'Ciudad', $values['ciudad']);
                ?>
                <div class="field">
                    <label for="categoria_id">Categoría</label>
                    <select id="categoria_id" name="categoria_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= (int) $category['id'] ?>" <?= (int) $values['categoria_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <label class="favorite-choice" for="favorito">
                    <input type="checkbox" id="favorito" name="favorito" value="1" <?= (int) $values['favorito'] === 1 ? 'checked' : '' ?>>
                    <span><?= icon('star') ?> Marcar como favorito</span>
                </label>
                <div class="form-actions">
                    <a href="dashboard.php" class="button button-quiet">Cancelar</a>
                    <button type="submit" class="button button-primary"><?= $id === null ? 'Guardar contacto' : 'Guardar cambios' ?></button>
                </div>
            </form>
        </section>
    </div>
    <?php page_end();
}
