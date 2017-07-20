<?php

/**
 * php-api config file
 */

# Env Mode : DEV, STAG, PROD (no cache file in DEV mode)
define('ENV_MODE', 'DEV');

# Tonic cache file
define('TONIC_CACHE', 'test-php-api');

# Private Key to encode JWT
define('SERVER_PRIVATE_KEY', 'wcscqsdcsdcsdcsdcsdcsdcsdcsdcsdcsdc');

# Tonic Folders Entry Points
define('LOAD_ENTRY_POINTS', [
    __DIR__ . '/TestApi.php'
]);

# Tonic Namespace Entry Points
define('MOUNT_ENTRY_POINTS', []);

# Api Demo URLs & Methods authorized calls
define('DEMO_AUTHORIZED_CALLS', [
    '/api/webpages/' => ['GET'],
    '/api/website/' => ['GET']
]);

# Api Demo URLs & Methods excluded calls
define('DEMO_EXCLUDED_CALLS', [
    '/api/comments' => ['GET', 'POST', 'PUT', 'DELETE'],
    '/api/users' => ['PUT', 'DELETE']
]);