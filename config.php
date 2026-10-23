<?php

function createDatabaseConnection(): PDO
{
    $host = '__DB_HOST__';
    $name = '__DB_NAME__';
    $user = '__DB_USER__';
    $password = '__DB_PASS__';

    if ($host === '__DB_HOST__' && $name === '__DB_NAME__' && $user === '__DB_USER__' && $password === '__DB_PASS__') {
        return createLocalDatabaseConnection();
    }

    if (in_array('', [$host, $name, $user, $password], true)
        || in_array('__DB_HOST__', [$host, $name, $user, $password], true)
        || in_array('__DB_NAME__', [$host, $name, $user, $password], true)
        || in_array('__DB_USER__', [$host, $name, $user, $password], true)
        || in_array('__DB_PASS__', [$host, $name, $user, $password], true)) {
        throw new RuntimeException('As credenciais do banco MySQL não foram configuradas.');
    }

    $pdo = new PDO(
        "mysql:host={$host};dbname={$name};charset=utf8mb4",
        $user,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}

function createLocalDatabaseConnection(): PDO
{
    $databaseFile = __DIR__ . '/database/mathplay.db';

    if (!file_exists($databaseFile) && !touch($databaseFile)) {
        throw new RuntimeException('Não foi possível criar o banco SQLite local.');
    }

    $pdo = new PDO('sqlite:' . $databaseFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}
