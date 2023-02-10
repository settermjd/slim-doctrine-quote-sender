<?php

namespace AppTest\Handler\Subscribe\Mobile;

use App\Domain\User;
use App\Handler\Subscribe\Mobile\MobileSubscribeRequestHandler;
use App\InputFilter\MobileNumberInputFilter;
use App\UserService;
use Laminas\Diactoros\Response\XmlResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MobileSubscribeRequestHandlerTest extends TestCase
{
    private MockObject|ServerRequestInterface $request;
    private MockObject|UserService $userService;

    public function setUp(): void
    {
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->userService = $this->createMock(UserService::class);
    }

    public function testCanSubscribeUserByMobile()
    {
        $mobileNumber = '+14155552672';
        $user = new User('', null, $mobileNumber);

        $this->userService
            ->expects($this->once())
            ->method('createWithMobileNumber')
            ->with($mobileNumber)
            ->willReturn($user);

        $handler = new MobileSubscribeRequestHandler($this->userService, new MobileNumberInputFilter());
        $this->request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'From' => $mobileNumber,
                'Body' => 'SUBSCRIBE',
            ]);
        $response = $this->createMock(ResponseInterface::class);

        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(XmlResponse::class, $result);

        $twiml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<Response><Message>You are now subscribed to the daily developer quotes service. 
To unsubscribe, send another SMS to this number with the text: UNSUBSCRIBE</Message></Response>

EOF;
        $this->assertSame($twiml, $result->getBody()->getContents());
    }

    /**
     * @dataProvider invalidMobileNumberProvider
     */
    public function testCannotSubscribeUserByMobileWithAnInvalidMobileNumber(string $mobileNumber = null)
    {
        $user = new User(null, null, $mobileNumber);

        $this->userService
            ->expects($this->never())
            ->method('createWithMobileNumber')
            ->with($mobileNumber)
            ->willReturn($user);

        $handler = new MobileSubscribeRequestHandler($this->userService, new MobileNumberInputFilter());
        $this->request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'From' => $mobileNumber,
                'Body' => 'SUBSCRIBE',
            ]);
        $response = $this->createMock(ResponseInterface::class);

        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(XmlResponse::class, $result);

        $twiml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<Response><Message>Mobile number must be in E.164 format. More information is available at https://www.twilio.com/docs/glossary/what-e164.</Message></Response>

EOF;
        $this->assertSame($twiml, $result->getBody()->getContents());
    }

    public static function invalidMobileNumberProvider()
    {
        return [
            [
                '04155552672',
            ],
        ];
    }
}
