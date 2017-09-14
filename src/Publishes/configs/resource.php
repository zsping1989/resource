<?php
/**
 * Created by PhpStorm.
 * User: 21498
 * Date: 2017/5/28
 * Time: 9:45
 */

return [
    //数据库配置添加
    'database'=>[
        'connections'=>[
            'schema' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => 'information_schema',
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]
        ]
    ]
];