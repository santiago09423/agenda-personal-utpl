<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

visitor_only();
$email = '';
$errors = [];

if (is_post()) {
    verify_csrf();
    $email = strtolower(text_field('correo'));
    $password = string_field($_POST, 'password');

    if (valid_email($email) && valid_password($password)) {
        $account = query('SELECT id, nombre, correo, password FROM usuarios WHERE correo = ?', [$email])->fetch();
        $hash = $account['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if (password_verify($password, $hash) && $account) {
            if (!session_regenerate_id(true)) {
                throw new RuntimeException('No se pudo renovar la sesión.');
            }
            $_SESSION = ['user' => [
                'id' => (int) $account['id'],
                'nombre' => $account['nombre'],
                'correo' => $account['correo'],
            ]];
            redirect('dashboard.php');
        }
    }
    $errors[] = 'El correo o la contraseña no coinciden. Revisa los datos e inténtalo otra vez.';
    http_response_code(422);
}

auth_start('Inicia sesión', 'Ingresa para ver los contactos que has guardado.');
flash_view();
errors_view($errors);
?>
<form method="post" action="login.php" class="stack">
    <?php
    csrf_input();
    field('correo', 'Correo electrónico', $email, 'email', 'username', 254);
    field('password', 'Contraseña', '', 'password', 'current-password', 72);
    ?>
    <button type="submit" class="button button-primary">Entrar a mi agenda</button>
</form>
<p class="auth-link">¿Aún no tienes cuenta? <a href="registro.php">Regístrate aquí</a>.</p>
<?php auth_end();
