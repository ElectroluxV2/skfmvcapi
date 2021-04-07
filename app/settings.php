<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => true, // Should be set to false in production
            'logger' => [
                'name' => 'skfmvcapi',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                'level' => Logger::DEBUG,
            ],
            'medoo' => [
                'database_type' => 'pgsql',
                'database_name' => 'osf',
                'server' => 'localhost',
                'username' => 'postgres',
                'password' => 'Budzisz1232-ma'
            ]
        ],
    ]);
};
