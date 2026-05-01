<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

// Prepare test database by cloning dev database
$projectDir = dirname(__DIR__);
$devDb = $projectDir . '/var/data.db';
$testDb = $projectDir . '/var/test.db';

if (file_exists($devDb)) {
    copy($devDb, $testDb);
}
