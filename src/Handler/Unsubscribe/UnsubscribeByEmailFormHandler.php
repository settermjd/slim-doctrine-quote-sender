<?php

declare(strict_types=1);

namespace App\Handler\Unsubscribe;

use App\Handler\FlashMessageHandlerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class UnsubscribeByEmailFormHandler
{
    use FlashMessageHandlerTrait;

    const TEMPLATE_NAME = 'app/unsubscribe-by-email';

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $view = Twig::fromRequest($request);

        $data = [];
        $data = $this->getFlashMessages($request, $data);

        return $view->render($response, self::TEMPLATE_NAME, $data);
    }

}