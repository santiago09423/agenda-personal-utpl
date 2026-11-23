<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$user = require_user();
$categories = categories();
$categoryIds = array_map(static fn(array $row): int => (int) $row['id'], $categories);

$search = trim(string_field($_GET, 'q'));
if (strlen($search) > 400) {
    problem(400, 'Búsqueda demasiado larga', 'Escribe un texto de búsqueda más corto.');
}
$field = string_field($_GET, 'campo');
$allowedFields = ['todos' => ['c.nombre', 'c.correo', 'c.ciudad'], 'nombre' => ['c.nombre'],
                  'correo' => ['c.correo'], 'ciudad' => ['c.ciudad']];
if (!isset($allowedFields[$field])) {
    $field = 'todos';
}
$sort = string_field($_GET, 'orden');
$allowedSorts = [
    'nombre_asc' => 'c.nombre ASC, c.id ASC',
    'nombre_desc' => 'c.nombre DESC, c.id DESC',
    'ciudad_asc' => 'c.ciudad ASC, c.nombre ASC, c.id ASC',
    'ciudad_desc' => 'c.ciudad DESC, c.nombre ASC, c.id ASC',
    'recientes' => 'c.fecha_creacion DESC, c.id DESC',
    'antiguos' => 'c.fecha_creacion ASC, c.id ASC',
];
if (!isset($allowedSorts[$sort])) {
    $sort = 'nombre_asc';
}
$rawCategory = string_field($_GET, 'categoria');
$categoryId = filter_var($rawCategory, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$categoryId = $categoryId !== false && in_array((int) $categoryId, $categoryIds, true) ? (int) $categoryId : 0;
$onlyFavorites = string_field($_GET, 'favoritos') === '1';

$where = ['c.id_usuario = ?'];
$params = [$user['id']];
if ($search !== '') {
    // Escapamos los comodines de LIKE: buscamos el texto realmente escrito.
    $pattern = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search) . '%';
    $conditions = [];
    foreach ($allowedFields[$field] as $column) {
        $conditions[] = "$column LIKE ? ESCAPE '!'";
        $params[] = $pattern;
    }
    $where[] = '(' . implode(' OR ', $conditions) . ')';
}
if ($categoryId !== 0) {
    $where[] = 'c.categoria_id = ?';
    $params[] = $categoryId;
}
if ($onlyFavorites) {
    $where[] = 'c.favorito = 1';
}
$sql = 'SELECT c.id, c.nombre, c.telefono, c.correo, c.ciudad, c.favorito, c.fecha_creacion,
               cat.nombre AS categoria
        FROM contactos c INNER JOIN categorias cat ON cat.id = c.categoria_id
        WHERE ' . implode(' AND ', $where) . ' ORDER BY ' . $allowedSorts[$sort];
$contacts = query($sql, $params)->fetchAll();
$totalContacts = (int) query('SELECT COUNT(*) FROM contactos WHERE id_usuario = ?', [$user['id']])->fetchColumn();
$totalFavorites = (int) query('SELECT COUNT(*) FROM contactos WHERE id_usuario = ? AND favorito = 1', [$user['id']])->fetchColumn();
$hasFilters = $search !== '' || $categoryId !== 0 || $onlyFavorites || $sort !== 'nombre_asc';

page_start('Mis contactos', $user);
?>
<section class="dashboard-intro">
    <div>
        <h1>Mis contactos</h1>
        <p class="subtitle">Aquí puedes buscar, organizar y actualizar tu agenda.</p>
    </div>
    <div class="profile">
        <span class="avatar"><?= avatar($user['nombre']) ?></span>
        <div><strong><?= e($user['nombre']) ?></strong><small><?= e($user['correo']) ?></small></div>
    </div>
</section>
<?php flash_view(); ?>
<section class="panel filter-panel" aria-labelledby="search-title">
    <div class="filter-header">
        <div><h2 id="search-title"><?= icon('search') ?> Encuentra un contacto</h2>
            <p class="filter-hint">Busca por nombre, correo o ciudad. También puedes filtrar por categoría.</p></div>
    </div>
    <form method="get" action="dashboard.php" class="filters">
        <div class="filter-group filter-wide">
            <label for="q">Buscar</label>
            <input id="q" name="q" type="search" value="<?= e($search) ?>" placeholder="Ej.: Andrea, ibarra o correo@ejemplo.com" maxlength="100">
        </div>
        <div class="filter-group">
            <label for="campo">Buscar en</label>
            <select id="campo" name="campo">
                <?php foreach (['todos' => 'Todos los campos', 'nombre' => 'Nombre', 'correo' => 'Correo', 'ciudad' => 'Ciudad'] as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $field === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="orden">Ordenar por</label>
            <select id="orden" name="orden">
                <?php foreach (['nombre_asc' => 'Nombre (A-Z)', 'nombre_desc' => 'Nombre (Z-A)',
                    'ciudad_asc' => 'Ciudad (A-Z)', 'ciudad_desc' => 'Ciudad (Z-A)',
                    'recientes' => 'Más recientes', 'antiguos' => 'Más antiguos'] as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="categoria">Categoría</label>
            <select id="categoria" name="categoria">
                <option value="">Todas</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <label class="filter-check">
            <input type="checkbox" name="favoritos" value="1" <?= $onlyFavorites ? 'checked' : '' ?>>
            <span>Solo favoritos</span>
        </label>
        <div class="filter-buttons">
            <button type="submit" class="button button-primary"><?= icon('filter') ?> Aplicar filtros</button>
            <?php if ($hasFilters): ?><a href="dashboard.php" class="button button-quiet">Limpiar</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="panel contact-list" aria-labelledby="list-title">
    <div class="list-header">
        <div class="list-title">
            <?= icon('users') ?><h2 id="list-title">Directorio</h2>
            <span class="count"><?= count($contacts) ?><?= $hasFilters ? ' encontrados' : '' ?></span>
        </div>
        <a href="crear.php" class="button button-primary"><?= icon('plus') ?> Nuevo contacto</a>
    </div>
    <p class="directory-summary">En tu agenda hay <?= $totalContacts ?> contacto<?= $totalContacts === 1 ? '' : 's' ?> y <?= $totalFavorites ?> favorito<?= $totalFavorites === 1 ? '' : 's' ?>.</p>
    <?php if ($contacts): ?>
        <div class="table-scroll">
        <table class="contacts">
            <thead><tr>
                <th scope="col">Nombre</th><th scope="col">Teléfono</th>
                <th scope="col">Correo</th><th scope="col">Ciudad</th>
                <th scope="col">Categoría</th><th scope="col">Acciones</th>
            </tr></thead>
            <tbody>
            <?php foreach ($contacts as $contact): ?>
                <tr>
                    <th scope="row">
                        <div class="contact-name"><span class="avatar"><?= avatar($contact['nombre']) ?></span>
                            <span><strong><?= e($contact['nombre']) ?></strong>
                            <small>Agregado: <?= e(date('d/m/Y', strtotime($contact['fecha_creacion']))) ?></small></span>
                        </div>
                    </th>
                    <td><span class="mobile-label" aria-hidden="true">Teléfono</span><?= e($contact['telefono']) ?></td>
                    <td><span class="mobile-label" aria-hidden="true">Correo</span><span class="break-word"><?= e($contact['correo']) ?></span></td>
                    <td><span class="mobile-label" aria-hidden="true">Ciudad</span><?= e($contact['ciudad']) ?></td>
                    <td><span class="mobile-label" aria-hidden="true">Categoría</span><span class="category-badge"><?= e($contact['categoria']) ?></span></td>
                    <td>
                        <div class="row-actions">
                            <form method="post" action="favorito.php">
                                <?php csrf_input(); ?>
                                <input type="hidden" name="id" value="<?= (int) $contact['id'] ?>">
                                <button type="submit" class="button square button-quiet <?= (int) $contact['favorito'] === 1 ? 'is-favorite' : '' ?>"
                                    aria-label="<?= (int) $contact['favorito'] === 1 ? 'Quitar de favoritos a ' : 'Marcar como favorito a ' ?><?= e($contact['nombre']) ?>"
                                    title="<?= (int) $contact['favorito'] === 1 ? 'Quitar favorito' : 'Marcar favorito' ?>"><?= icon('star') ?></button>
                            </form>
                            <a class="button square button-quiet" href="editar.php?id=<?= (int) $contact['id'] ?>" aria-label="Editar a <?= e($contact['nombre']) ?>" title="Editar"><?= icon('edit') ?></a>
                            <a class="button square button-quiet danger-text" href="confirmar.php?id=<?= (int) $contact['id'] ?>" aria-label="Eliminar a <?= e($contact['nombre']) ?>" title="Eliminar"><?= icon('trash') ?></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php else: ?>
        <div class="empty">
            <span class="empty-icon"><?= $hasFilters ? icon('search') : icon('book') ?></span>
            <?php if ($hasFilters): ?>
                <h3>No encontramos contactos con esos filtros</h3>
                <p>Prueba con otra palabra o quita alguno de los filtros.</p>
                <a href="dashboard.php" class="button button-quiet">Ver todos mis contactos</a>
            <?php else: ?>
                <h3>Tu agenda todavía está vacía</h3>
                <p>Empieza guardando a alguien que quieras tener a mano.</p>
                <a href="crear.php" class="button button-quiet"><?= icon('plus') ?> Agregar contacto</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>
<?php page_end();
