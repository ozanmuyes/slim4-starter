<?php
declare(strict_types=1);

namespace App\Middleware;

// From https://github.com/slimphp/Slim-Skeleton/blob/master/src/Application/Middleware/SessionMiddleware.php#L6-L25
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            session_start();
            $request = $request->withAttribute('session', $_SESSION);
        }

        return $handler->handle($request);
    }
}
