<?php

function createDatabaseConnection(): PDO
{
    $databaseFile = __DIR__ . '/database/mathplay.db';

    if (!file_exists($databaseFile)) {
        touch($databaseFile);
    }

    $pdo = new PDO('sqlite:' . $databaseFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
}
