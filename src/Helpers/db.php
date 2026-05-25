<?php

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        // Cloud SQL via Unix socket when DB_HOST starts with '/'
        // e.g. /cloudsql/project:region:instance
        $dsn = str_starts_with(DB_HOST, '/')
            ? sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET)
            : sprintf('mysql:host=%s;dbname=%s;charset=%s',        DB_HOST, DB_NAME, DB_CHARSET);

        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
