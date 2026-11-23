<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
redirect(isset($_SESSION['user']) ? 'dashboard.php' : 'login.php');
