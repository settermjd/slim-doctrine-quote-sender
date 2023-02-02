<?php

declare(strict_types=1);

namespace App\Handler\Subscribe;

use App\UserService;
use Laminas\Diactoros\Response\XmlResponse;
use Psr\Http\Message\{ResponseInterface,ServerRequestInterface};
use Twilio\TwiML\MessagingResponse;

/**
 * This class subscribes a user to the service using their mobile number
 */
class SubscribeByMobileHandler
{
    public const RESPONSE_MESSAGE = <<<EOF
You are now subscribed to the daily developer quotes service. 
To unsubscribe, send another SMS to this number with the text: UNSUBSCRIBE
EOF;

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getParsedBody();
        $this->userService->createWithMobileNumber($params['From']);

        $twiml = new MessagingResponse();
        $twiml->message(self::RESPONSE_MESSAGE);
        return new XmlResponse($twiml->asXML());
    }
}