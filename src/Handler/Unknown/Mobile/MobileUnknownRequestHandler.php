<?php

declare(strict_types=1);

namespace App\Handler\Unknown\Mobile;

use App\InputFilter\MobileInputTrait;
use Laminas\Diactoros\Response\XmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twilio\TwiML\MessagingResponse;

class MobileUnknownRequestHandler
{
    use MobileInputTrait;

    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $twiml = new MessagingResponse();
        $twiml->message(self::RESPONSE_UNKNOWN_REQUEST);

        return new XmlResponse($twiml->asXML(), 400);
    }
}