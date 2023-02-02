<?php

namespace AppTest\Handler\Subscribe;

use App\Domain\User;
use App\Handler\Subscribe\SubscribeByMobileHandler;
use App\UserService;
use Laminas\Diactoros\Response\XmlResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SubscribeByMobileHandlerTest extends TestCase
{
    public function testCanSubscribeUserByMobile()
    {
        $mobileNumber = '+14155552672';
        $user = new User('', null, $mobileNumber);

        $userService = $this->createMock(UserService::class);
        $userService
            ->expects($this->once())
            ->method('createWithMobileNumber')
            ->with($mobileNumber)
            ->willReturn($user);

        $handler = new SubscribeByMobileHandler($userService);
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'From' => $mobileNumber,
                'Body' => 'SUBSCRIBE',
            ]);
        $response = $this->createMock(ResponseInterface::class);

        $result = $handler->handle($request, $response, []);

        $this->assertInstanceOf(XmlResponse::class, $result);

        $twiml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<Response><Message>You are now subscribed to the daily developer quotes service. 
To unsubscribe, send another SMS to this number with the text: UNSUBSCRIBE</Message></Response>

EOF;
        $this->assertSame($twiml, $result->getBody()->getContents());
    }
}
