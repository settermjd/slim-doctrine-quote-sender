<?php

declare(strict_types=1);

namespace App\Handler\Unsubscribe;

use App\Handler\EmailHandlerTrait;
use App\UserService;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\InputFilter\InputFilterInterface;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UnsubscribeByEmailHandler
{
    use EmailHandlerTrait;

    public function __construct(private UserService $userService, private InputFilterInterface $inputFilter)
    {
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
                    $this->inputFilter->getValue('email')
                );

            $flashMessage->flash('status', self::RESPONSE_MESSAGE_UNSUBSCRIBE_SUCCESS);
        } else {
            $flashMessage->flash('status', self::RESPONSE_MESSAGE_FAIL_INVALID_EMAIL);
        }

        return new RedirectResponse('/unsubscribe/by-email-address');
    }

}