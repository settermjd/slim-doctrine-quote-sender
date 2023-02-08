<?php

declare(strict_types=1);

namespace App\Handler;

use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ServerRequestInterface;

trait FlashMessageHandlerTrait
{
    public function getFlashMessages(ServerRequestInterface $request, array $data): array
    {
        /** @var FlashMessagesInterface $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        if (!is_null($flashMessage)) {
            $flashes = $flashMessage->getFlashes();
            if (array_key_exists('status', $flashes)) {
                $data['status'] = $flashes['status'];
            }
            if (array_key_exists('error', $flashes)) {
                $data['error'] = $flashes['error'];
            }
        }

        return $data;
    }
}