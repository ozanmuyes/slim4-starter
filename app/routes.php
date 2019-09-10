<?php
declare(strict_types=1);

use App\Middleware\APIEmitterMiddleware;
use App\View;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->get('/', function(Request $request, Response $response, View $view) {
        return $view->render($response, 'index.twig');
    });

    $app->group('/api', function (Group $group) {
        $group->get('', function (Response $response) {
            $resBody = ['hello' => 'world'];
            $response->getBody()->write(json_encode($resBody));
            return $response;
        });

        //
    })->add(new APIEmitterMiddleware());

    //
};
