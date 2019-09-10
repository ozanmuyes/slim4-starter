<?php
declare(strict_types=1);

namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Twig\Environment as Twig;

class View
{
    /** @var Twig $twig */
    private $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function render(Response $response, $templateName, $data = [])
    {
        $template = $this->twig->load($templateName);
        $rendered = $template->render($data);

        $response->getBody()->write($rendered);
        return $response;
    }
}
