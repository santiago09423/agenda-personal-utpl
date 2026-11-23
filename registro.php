<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

visitor_only();
$name = $email = '';
$errors = [];

if (is_post()) {
    verify_csrf();
    $name = text_field('nombre');
    $email = strtolower(text_field('correo'));
    $password = string_field($_POST, 'password');
    $confirmation = string_field($_POST, 'confirmacion');

    if (!valid_text($name)) {
        $errors[] = 'Escribe tu nombre (máximo 100 caracteres).';
    }
    if (!valid_email($email)) {
        $errors[] = 'Ingresa un correo electrónico válido.';
    }
    if (!valid_password($password)) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres y un máximo de 72 bytes.';
    }
    if ($password !== $confirmation) {
        $errors[] = 'Las dos contraseñas deben ser iguales.';
    }

    if (!$errors) {
        try {
            query('INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)',
                [$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['flash'] = 'Tu cuenta ya está creada. Ahora puedes iniciar sesión.';
            redirect('login.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) !== 1062) {
                throw $exception;
            }
            $errors[] = 'Este correo ya tiene una cuenta. Puedes iniciar sesión directamente.';
        }
    }
    http_response_code(422);
}

auth_start('Crea tu cuenta', 'Regístrate para guardar tus contactos en un solo lugar.');
errors_view($errors);
?>
<form method="post" action="registro.php" class="stack">
    <?php
    csrf_input();
    field('nombre', 'Nombre completo', $name, 'text', 'name');
    field('correo', 'Correo electrónico', $email, 'email', 'email', 254);
    field('password', 'Contraseña', '', 'password', 'new-password', 72, 'Usa al menos 8 caracteres.');
    field('confirmacion', 'Repetir contraseña', '', 'password', 'new-password', 72);
    ?>
    <button type="submit" class="button button-primary">Crear cuenta</button>
</form>
<p class="auth-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>.</p>
<?php auth_end();
