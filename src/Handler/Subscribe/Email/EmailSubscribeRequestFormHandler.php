<?php

declare(strict_types=1);

namespace App\Handler\Subscribe\Email;

use App\Handler\FlashMessageHandlerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class EmailSubscribeRequestFormHandler
{
    use FlashMessageHandlerTrait;

    public const TEMPLATE_NAME = 'app/subscribe-by-email.html.twig';

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        $data = [];
        $data = $this->getFlashMessages($request, $data);

        return $view->render($response, self::TEMPLATE_NAME, $data);
    }
}