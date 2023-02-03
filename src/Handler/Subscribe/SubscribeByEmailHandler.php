<?php

namespace App\Handler\Subscribe;

use App\UserService;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\InputFilter\InputFilterInterface;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SubscribeByEmailHandler
{
    private InputFilterInterface $inputFilter;
    private UserService $userService;

    public function __construct(UserService $userService, InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getParsedBody();
        $this->inputFilter->setData($params);

        /** @var FlashMessagesInterface $flashMessage */
        $flashMessage = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

        if ($this->inputFilter->isValid()) {
            $this->userService
                ->createWithEmailAddress(
                    $this->inputFilter->getValue('email')
                );

            $flashMessage->flash('status', 'You were successfully subscribed');
        } else {
            $flashMessage->flash('status', 'The email address provided is not a valid email address.');
        }

        return new RedirectResponse('/api/subscribe/by-email-address');
    }
}