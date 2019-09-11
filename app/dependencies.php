<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        Psr\Log\LoggerInterface::class => function (ContainerInterface $c) {
            /** @var array $settings */
            $settings = $c->get('settings')['logger'];

            $logger = new Monolog\Logger($settings['name']);

            $logger->pushProcessor(new Monolog\Processor\UidProcessor());
            $logger->pushProcessor(new Monolog\Processor\WebProcessor());
            $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

            return $logger;
        },

        // NOTE As of writing this project Slim-Twig for Slim 4 was beta,
        //      in order to use Twig we have these two dependencies.
        Twig\Environment::class => function (ContainerInterface $c) {
            /** @var array $settings */
            $settings = $c->get('settings')['view'];

            $loader = new \Twig\Loader\FilesystemLoader($settings['template_path']);
            $twig = new \Twig\Environment($loader, $settings['twig']);

            // TODO Add Twig functions (like in the Slim's View-Twig)

            return $twig;
        },
        App\View::class => function (ContainerInterface $c) {
            $twig = $c->get(Twig\Environment::class);
            $view = new App\View($twig);

            return $view;
        },

        //
    ]);
};
