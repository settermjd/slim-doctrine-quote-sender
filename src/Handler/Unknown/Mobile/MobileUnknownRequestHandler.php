<?php

declare(strict_types=1);

namespace App\Handler\Unknown\Mobile;

use Laminas\Diactoros\Response\XmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twilio\TwiML\MessagingResponse;

class MobileUnknownRequestHandler
{
    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $error = <<<EIOF
Sorry, but that message was not understood. To subscribe, send an SMS to this number with the word SUBSCRIBE. To unsubscribe, send an SMS to this number with the word UNSUBSCRIBE.
EIOF;

        $twiml = new MessagingResponse();
        $twiml->message($error);

        return new XmlResponse($twiml->asXML(), 400);
    }
}