<?php

declare(strict_types=1);

namespace App\Handler\Unsubscribe;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UnsubscribeByEmailFormHandler
{
    const TEMPLATE_NAME = 'app::unsubscribe-by-email';

    public function __construct(private TemplateRendererInterface $renderer)
    {
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $createMock, array $args)
    {
        $data = [];

        /** @var FlashMessagesInterface $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        if (! is_null($flashMessage) && ! is_null($flashMessage->getFlash('status'))) {
            $data['status'] = $flashMessage->getFlash('status');
        }

        return new HtmlResponse($this->renderer->render(self::TEMPLATE_NAME, $data));
    }
}