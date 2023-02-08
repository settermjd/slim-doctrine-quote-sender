<?php

declare(strict_types=1);

namespace App\Handler\Subscribe;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SubscribeByEmailFormHandler
{
    public function __construct(private TemplateRendererInterface $renderer)
    {
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
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

        return new HtmlResponse($this->renderer->render('app::subscribe-by-email', $data));
    }
}