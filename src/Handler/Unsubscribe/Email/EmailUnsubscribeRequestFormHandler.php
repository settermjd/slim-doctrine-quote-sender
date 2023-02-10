<?php

declare(strict_types=1);

namespace App\Handler\Unsubscribe\Email;

use App\Handler\EmailHandlerTrait;
use App\Handler\FlashMessageHandlerTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class EmailUnsubscribeRequestFormHandler
{
    use EmailHandlerTrait,
        FlashMessageHandlerTrait;

    const TEMPLATE_NAME = 'app/unsubscribe-by-email.html.twig';

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $view = Twig::fromRequest($request);

        $data = [
            'action_route' => self::ROUTE_UNSUBSCRIBE,
        ];
        $data = $this->getFlashMessages($request, $data);

        return $view->render($response, self::TEMPLATE_NAME, $data);
    }

}