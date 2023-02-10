<?php

namespace AppTest\Handler;

use App\Handler\MobileUnknownRequestHandler;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\XmlResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MobileUnknownRequestHandlerTest extends TestCase
{
    public function testCanRespondCorrectly()
    {
        $handler = new MobileUnknownRequestHandler();
        $response = $handler->handle(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(ResponseInterface::class),
            []
        );

        $twiml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<Response><Message>Sorry, but that message was not understood. To subscribe, send an SMS to this number with the word SUBSCRIBE. To unsubscribe, send an SMS to this number with the word UNSUBSCRIBE.</Message></Response>

EOF;

        $this->assertInstanceOf(XmlResponse::class, $response);
        $this->assertSame($twiml, $response->getBody()->getContents());
        $this->assertSame(400, $response->getStatusCode());
    }
}
