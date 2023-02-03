<?php

namespace App\Handler\Subscribe;

use App\UserService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\InputFilter\InputFilterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SubscribeByEmailHandler
{
    private UserService $userService;
    private InputFilterInterface $inputFilter;

    public function __construct(UserService $userService, InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getParsedBody();
        $this->inputFilter->setData($params);

        if ($this->inputFilter->isValid()) {
            $this->userService
                ->createWithEmailAddress(
                    $this->inputFilter->getValue('email')
                );

            return new RedirectResponse('/api/subscribe/by-email-address');
        }
    }
}