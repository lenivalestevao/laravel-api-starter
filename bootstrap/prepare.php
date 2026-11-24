<?php

/*
 * Runs automatically from Composer (pre-autoload-dump).
 * Creates the folders Laravel needs to write to, plus a local .env file
 * and an empty SQLite database, so a fresh clone works after `composer install`.
 */

$root = dirname(__DIR__);

$directories = [
    'bootstrap/cache',
    'database',
    'storage/app/private',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
];

foreach ($directories as $directory) {
    $path = $root.'/'.$directory;

    if (! is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

if (! file_exists($root.'/.env') && file_exists($root.'/.env.example')) {
    copy($root.'/.env.example', $root.'/.env');
}

if (! file_exists($root.'/database/database.sqlite')) {
    touch($root.'/database/database.sqlite');
}
