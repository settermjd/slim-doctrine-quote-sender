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
    public const REGEX_MOBILE_NUMBER = '^\+[1-9]\d{1,14}$';
    public const RESPONSE_MESSAGE_SUCCESSFULLY_SUBSCRIBED = <<<EOF
You are now subscribed to the daily developer quotes service. 
To unsubscribe, send another SMS to this number with the text: UNSUBSCRIBE
EOF;
    public const RESPONSE_MESSAGE_INVALID_MOBILE_NUMBER = 'Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.';

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $params = $request->getParsedBody();
        $twiml = new MessagingResponse();

        if (! is_null($params['From'])
            && ! preg_match(sprintf('/%s/', self::REGEX_MOBILE_NUMBER), $params['From'])
        ) {
            $twiml->message(self::RESPONSE_MESSAGE_INVALID_MOBILE_NUMBER);
            return new XmlResponse($twiml->asXML());
        }

        $this->userService->createWithMobileNumber($params['From']);
        $twiml->message(self::RESPONSE_MESSAGE_SUCCESSFULLY_SUBSCRIBED);
        return new XmlResponse($twiml->asXML());
    }
}