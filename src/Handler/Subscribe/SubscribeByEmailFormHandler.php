<?php

declare(strict_types=1);

namespace App\Handler\Subscribe;

use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class SubscribeByEmailFormHandler
{
    public const TEMPLATE_NAME = 'app/subscribe-by-email.html.twig';

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $view = Twig::fromRequest($request);

        $data = [];

        /** @var FlashMessagesInterface $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        if (! is_null($flashMessage)) {
            $flashes = $flashMessage->getFlashes();
            if (array_key_exists('status', $flashes)) {
                $data['status'] = $flashes['status'];
            }
            if (array_key_exists('error', $flashes)) {
                $data['error'] = $flashes['error'];
            }
        }

        return $view->render($response, self::TEMPLATE_NAME, $data);
    }
}