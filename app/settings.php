<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => getenv('APP_ENV') === 'development',

            'logger' => [
                'name' => 'slim-app',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../var/logs/app.log',
                'level' => Logger::DEBUG,
            ],

            'view' => [
                'template_path' => __DIR__ . '/templates',
                'twig' => [
                    'cache' => __DIR__ . '/../var/cache/twig',
                    'debug' => true,
                    'auto_reload' => true,
                ],
            ],

            //
        ],
    ]);
};
