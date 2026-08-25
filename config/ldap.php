<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LDAP Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the LDAP connections below you wish
    | to use as your default connection for all LDAP operations. Of
    | course you may add as many connections you'd like below.
    |
    */

    'default' => env('LDAP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | LDAP Connections
    |--------------------------------------------------------------------------
    |
    | Below you may configure each LDAP connection your application requires
    | access to. Be sure to include a valid base DN - otherwise you may
    | not receive any results when performing LDAP search operations.
    |
    */

    'connections' => [

        'default' => [
            'hosts' => [env('LDAP_DEFAULT_HOSTS', env('LDAP_HOST', '127.0.0.1'))],
            'username' => env('LDAP_DEFAULT_USERNAME', env('LDAP_USERNAME', 'cn=user,dc=local,dc=com')),
            'password' => env('LDAP_DEFAULT_PASSWORD', env('LDAP_PASSWORD', 'secret')),
            'port' => (int) env('LDAP_DEFAULT_PORT', env('LDAP_PORT', 389)),
            'base_dn' => env('LDAP_DEFAULT_BASE_DN', env('LDAP_BASE_DN', 'dc=local,dc=com')),
            'timeout' => (int) env('LDAP_DEFAULT_TIMEOUT', env('LDAP_TIMEOUT', 5)),
            'use_ssl' => (bool) env('LDAP_DEFAULT_SSL', env('LDAP_SSL', false)),
            'use_tls' => (bool) env('LDAP_DEFAULT_TLS', env('LDAP_TLS', false)),
            'use_sasl' => env('LDAP_SASL', false),
            'sasl_options' => [
                // 'mech' => 'GSSAPI',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Logging
    |--------------------------------------------------------------------------
    |
    | When LDAP logging is enabled, all LDAP search and authentication
    | operations are logged using the default application logging
    | driver. This can assist in debugging issues and more.
    |
    */

    'logging' => [
        'enabled' => env('LDAP_LOGGING', true),
        'channel' => env('LOG_CHANNEL', 'stack'),
        'level' => env('LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Cache
    |--------------------------------------------------------------------------
    |
    | LDAP caching enables the ability of caching search results using the
    | query builder. This is great for running expensive operations that
    | may take many seconds to complete, such as a pagination request.
    |
    */

    'cache' => [
        'enabled' => env('LDAP_CACHE', false),
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

];
