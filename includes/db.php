<?php
declare(strict_types=1);

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $config = require __DIR__ . '/../config/database.php';
    $local = __DIR__ . '/../config/database.local.php';
    if (is_file($local)) {
        $custom = require $local;
        if (!is_array($custom)) {
            throw new RuntimeException('La configuración privada no es válida.');
        }
        $config = array_replace($config, $custom);
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'], (int) $config['port'], $config['name']);
    $connection = new PDO($dsn, $config['user'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $connection;
}

function query(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement;
}
