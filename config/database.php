<?php

$mysqlAttrSslCa = defined('\Pdo\Mysql::ATTR_SSL_CA')
    ? \Pdo\Mysql::ATTR_SSL_CA
    : \PDO::MYSQL_ATTR_SSL_CA;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | The platform always defaults to the central MySQL database connection.
    | Tenant connections are provisioned and swapped at runtime only after
    | tenant domain resolution has succeeded against the central registry.
    |
    */

    'default' => env('DB_CONNECTION', 'central'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'central' => [
            'driver' => 'mysql',
            'url' => env('CENTRAL_DATABASE_URL'),
            'host' => env('CENTRAL_DB_HOST', '127.0.0.1'),
            'port' => env('CENTRAL_DB_PORT', '3306'),
            'database' => env('CENTRAL_DB_DATABASE', 'webuildyouthrive_central'),
            'username' => env('CENTRAL_DB_USERNAME', 'root'),
            'password' => env('CENTRAL_DB_PASSWORD', ''),
            'unix_socket' => env('CENTRAL_DB_SOCKET', ''),
            'charset' => env('CENTRAL_DB_CHARSET', 'utf8mb4'),
            'collation' => env('CENTRAL_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'timezone' => env('CENTRAL_DB_TIMEZONE', '+00:00'),
            'engine' => 'InnoDB',
            'modes' => [
                'STRICT_TRANS_TABLES',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_ENGINE_SUBSTITUTION',
            ],
            'sslmode' => env('CENTRAL_DB_SSL_MODE', 'preferred'),
            'options' => extension_loaded('pdo_mysql')
                ? array_filter([
                    PDO::ATTR_TIMEOUT => (int) env('CENTRAL_DB_CONNECT_TIMEOUT', 5),
                    $mysqlAttrSslCa => env('CENTRAL_DB_SSL_CA'),
                ])
                : [],
        ],

        'tenant' => [
            'driver' => 'mysql',
            'host' => null,
            'port' => env('TENANT_DB_PORT', '3306'),
            'database' => null,
            'username' => null,
            'password' => null,
            'unix_socket' => '',
            'charset' => env('TENANT_DB_CHARSET', 'utf8mb4'),
            'collation' => env('TENANT_DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'timezone' => env('TENANT_DB_TIMEZONE', '+00:00'),
            'engine' => 'InnoDB',
            'modes' => [
                'STRICT_TRANS_TABLES',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_ENGINE_SUBSTITUTION',
            ],
            'sslmode' => env('TENANT_DB_SSL_MODE', 'preferred'),
            'options' => extension_loaded('pdo_mysql')
                ? array_filter([
                    PDO::ATTR_TIMEOUT => (int) env('TENANT_DB_CONNECT_TIMEOUT', 5),
                    $mysqlAttrSslCa => env('TENANT_DB_SSL_CA'),
                ])
                : [],
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', env('CENTRAL_DB_HOST', '127.0.0.1')),
            'port' => env('DB_PORT', env('CENTRAL_DB_PORT', '3306')),
            'database' => env('DB_DATABASE', env('CENTRAL_DB_DATABASE', 'webuildyouthrive_central')),
            'username' => env('DB_USERNAME', env('CENTRAL_DB_USERNAME', 'root')),
            'password' => env('DB_PASSWORD', env('CENTRAL_DB_PASSWORD', '')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', env('CENTRAL_DB_CHARSET', 'utf8mb4')),
            'collation' => env('DB_COLLATION', env('CENTRAL_DB_COLLATION', 'utf8mb4_unicode_ci')),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'timezone' => env('DB_TIMEZONE', env('CENTRAL_DB_TIMEZONE', '+00:00')),
            'engine' => 'InnoDB',
            'modes' => [
                'STRICT_TRANS_TABLES',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_ENGINE_SUBSTITUTION',
            ],
            'options' => extension_loaded('pdo_mysql')
                ? array_filter([
                    PDO::ATTR_TIMEOUT => (int) env('DB_CONNECT_TIMEOUT', 5),
                    $mysqlAttrSslCa => env('MYSQL_ATTR_SSL_CA'),
                ])
                : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', 'wbyt-redis-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'session' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_SESSION_DB', '2'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'queue' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_QUEUE_DB', '3'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
