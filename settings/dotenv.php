<?php

define('APP_NAME', $_ENV['APP_NAME']);
define('APP_ENV', $_ENV['APP_ENV']);
define('APP_DEBUG', $_ENV['APP_DEBUG']);
define('APP_URL', $_ENV['APP_URL']);
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE']);
define('APP_LOCALE', $_ENV['APP_LOCALE']);
define('APP_FALLBACK_LOCALE', $_ENV['APP_FALLBACK_LOCALE']);

define('PRODUCTION', APP_ENV === 'production');
define('DEBUG', APP_DEBUG === 'true' || !PRODUCTION);

if(!defined('CLI_RUNNER')) define('CLI_RUNNER', false);

if(PRODUCTION) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);

    // database credentials
    define('DB_DRIVER', $_ENV['PROD_DB_DRIVER']);
    define('DB_HOST', $_ENV['PROD_DB_HOST']);
    define('DB_USER', $_ENV['PROD_DB_USER']);
    define('DB_PASS', $_ENV['PROD_DB_PASS']);
    define('DB_NAME', $_ENV['PROD_DB_NAME']);
    define('DB_PORT', $_ENV['PROD_DB_PORT']);
    define('DB_CHARSET', $_ENV['PROD_DB_CHARSET']);
    define('DB_COLLATION', $_ENV['PROD_DB_COLLATION']);
    define('DB_PREFIX', $_ENV['PROD_DB_PREFIX']);

    define('LOG_PATH', BASE_DIR . '/storage/logs/production.log');
    define('JSON_LOG_PATH', BASE_DIR . '/storage/logs/production.json');
    define('CACHE_PATH', BASE_DIR . '/storage/cache/production');
    define('SESSION_PATH', BASE_DIR . '/storage/sessions/production');
    define('UPLOAD_PATH', BASE_DIR . '/storage/uploads/production');
} else {
    // database credentials
    define('DB_DRIVER', $_ENV['DEV_DB_DRIVER']);
    define('DB_HOST', $_ENV['DEV_DB_HOST']);
    define('DB_USER', $_ENV['DEV_DB_USER']);
    define('DB_PASS', $_ENV['DEV_DB_PASS']);
    define('DB_NAME', $_ENV['DEV_DB_NAME']);
    define('DB_PORT', $_ENV['DEV_DB_PORT']);
    define('DB_CHARSET', $_ENV['DEV_DB_CHARSET']);
    define('DB_COLLATION', $_ENV['DEV_DB_COLLATION']);
    define('DB_PREFIX', $_ENV['DEV_DB_PREFIX']);

    define('LOG_PATH', BASE_DIR . '/storage/logs/development.log');
    define('JSON_LOG_PATH', BASE_DIR . '/storage/logs/development.json');
    define('CACHE_PATH', BASE_DIR . '/storage/cache/development');
    define('SESSION_PATH', BASE_DIR . '/storage/sessions/development');
    define('UPLOAD_PATH', BASE_DIR . '/storage/uploads/development');
}

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}










