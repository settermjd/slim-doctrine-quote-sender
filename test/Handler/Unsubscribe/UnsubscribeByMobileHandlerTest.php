<?php

namespace AppTest\Handler\Unsubscribe;

use App\Handler\Unsubscribe\UnsubscribeByMobileHandler;
use App\InputFilter\MobileNumberInputFilter;
use App\UserService;
use Laminas\Diactoros\Response\XmlResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UnsubscribeByMobileHandlerTest extends TestCase
{
    private MockObject|ServerRequestInterface $request;
    private MockObject|UserService $userService;

    public function setUp(): void
    {
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->userService = $this->createMock(UserService::class);
    }

    public function testCanUnsubscribeUserByMobileNumber()
    {
        $mobileNumber = '+14155552672';
        $this->userService
            ->expects($this->once())
            ->method('removeByMobileNumber')
            ->with($mobileNumber)
            ->willReturn(true);
        $this->request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'From' => $mobileNumber,
                'Body' => 'UNSUBSCRIBE',
            ]);
        $response = $this->createMock(ResponseInterface::class);

        $handler = new UnsubscribeByMobileHandler($this->userService, new MobileNumberInputFilter());
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(XmlResponse::class, $result);

        $twiml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<Response><Message>You are now unsubscribed from the daily developer quotes service. 
To resubscribe, send another SMS to this number with the text: SUBSCRIBE.</Message></Response>

EOF;
        $this->assertSame($twiml, $result->getBody()->getContents());
    }

    public function testCannotUnsubscribeUserWithAnInvalidMobileNumber()
    {
        $mobileNumber = '0014155552672';
        $this->userService
            ->expects($this->never())
            ->method('removeByMobileNumber');
        $this->request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'From' => $mobileNumber,
                'Body' => 'UNSUBSCRIBE',
            ]);
        $response = $this->createMock(ResponseInterface::class);

        $handler = new UnsubscribeByMobileHandler($this->userService, new MobileNumberInputFilter());
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(XmlResponse::class, $result);

        $twiml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<Response><Message>Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.</Message></Response>

EOF;
        $this->assertSame($twiml, $result->getBody()->getContents());
    }

}
