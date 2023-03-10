<?php

declare(strict_types=1);

namespace App\Handler\Unsubscribe\Email;

use App\Handler\EmailHandlerTrait;
use App\Service\UserService;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\InputFilter\InputFilterInterface;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EmailUnsubscribeRequestHandler
{
    use EmailHandlerTrait;

    public function __construct(
        private readonly UserService $userService,
        private readonly InputFilterInterface $inputFilter
    ) {
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getParsedBody();
        $this->inputFilter->setData($params);

        /** @var FlashMessagesInterface $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

        if ($this->inputFilter->isValid()) {
            $this->userService
                ->removeByEmailAddress(
                    $this->inputFilter->getValue('emailAddress')
                );

            $flashMessage->flash('status', self::RESPONSE_MESSAGE_UNSUBSCRIBE_SUCCESS);
        } else {
            $flashMessage->flash('status', self::RESPONSE_MESSAGE_FAIL_INVALID_EMAIL);
        }

        return new RedirectResponse(self::ROUTE_UNSUBSCRIBE);
    }

}