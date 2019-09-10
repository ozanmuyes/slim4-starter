<?php
declare(strict_types=1);

ini_set("log_errors", '1');
ini_set("error_log", __DIR__ . '/../var/logs/error.log');

use App\Handlers\HttpErrorHandler;
use App\Handlers\ShutdownHandler;
use App\ResponseEmitter;
use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

require __DIR__ . "/../vendor/autoload.php";

// Load .env file
$dotenv = Dotenv::create(__DIR__ . '/../');
$dotenv->load();

/*
// Instantiate and register Whoops
if (getenv('APP_DEBUG') === 'true') {
    $whoops = new Whoops\Run();

    $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());

    // Set Whoops as the default error and exception handler used by PHP:
    $whoops->register();
} // ...actually let the server handle the 500 (no `else` here)
*/

// Instantiate the container builder
$containerBuilder = new ContainerBuilder();
if (getenv('APP_ENV') === 'production') {
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// https://github.com/slimphp/Slim-Skeleton/blob/master/public/index.php#L24-L26
// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// TODO https://github.com/slimphp/Slim-Skeleton/blob/master/public/index.php#L28-L30

// Build the container
$container = $containerBuilder->build();

// Instantiate the app
/*AppFactory::setContainer($container);
$app = AppFactory::create();*/
$app = DI\Bridge\Slim\Bridge::create($container);
$callableResolver = $app->getCallableResolver();

// Register middleware
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($app);

// https://github.com/slimphp/Slim-Skeleton/blob/master/public/index.php#L44-L46
// Register routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

// https://github.com/slimphp/Slim-Skeleton/blob/master/public/index.php#L48-L73
/** @var bool $displayErrorDetails */
$displayErrorDetails = $container->get('settings')['displayErrorDetails'];

// Create Request object from globals
$psr17Factory = new Psr17Factory();
$creator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);
$request = $creator->fromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
