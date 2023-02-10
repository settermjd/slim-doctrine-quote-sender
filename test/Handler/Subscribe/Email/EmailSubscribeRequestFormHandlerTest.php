<?php

namespace AppTest\Handler\Subscribe\Email;

use App\Handler\EmailHandlerTrait;
use App\Handler\Subscribe\Email\EmailSubscribeRequestFormHandler;
use Mezzio\Flash\FlashMessagesInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class EmailSubscribeRequestFormHandlerTest extends TestCase
{
    use EmailHandlerTrait;

    private ServerRequestInterface|MockObject $request;

    public function setUp(): void
    {
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    public function testCanSuccessfullyHandleRequests()
    {
        $response = $this->createMock(ResponseInterface::class);

        $twig = $this->createMock(Twig::class);
        $twig
            ->expects($this->once())
            ->method('render')
            ->with(
                $response,
                EmailSubscribeRequestFormHandler::TEMPLATE_NAME,
                $this->isType('array')
            )
            ->willReturn($this->createMock(ResponseInterface::class));

        $flashMessage = $this->createMock(FlashMessagesInterface::class);
        $flashMessage
            ->expects($this->once())
            ->method('getFlashes')
            ->willReturn(
                ['status' => self::RESPONSE_MESSAGE_SUBSCRIBE_SUCCESS]
            );

        $this->request
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnOnConsecutiveCalls($twig, $flashMessage);

        $handler = new EmailSubscribeRequestFormHandler();
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * @dataProvider flashMessageProvider
     */
    public function testWillRenderFlashMessageIfMessageIsAvailable(array $flashes)
    {
        $response = $this->createMock(ResponseInterface::class);

        $twig = $this->createMock(Twig::class);
        $twig
            ->expects($this->once())
            ->method('render')
            ->with(
                $response,
                EmailSubscribeRequestFormHandler::TEMPLATE_NAME,
                $flashes
            )
            ->willReturn($this->createMock(ResponseInterface::class));

        $flashMessage = $this->createMock(FlashMessagesInterface::class);
        $flashMessage
            ->expects($this->once())
            ->method('getFlashes')
            ->willReturn($flashes);

        $this->request
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnOnConsecutiveCalls($twig, $flashMessage);

        $handler = new EmailSubscribeRequestFormHandler();
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public static function flashMessageProvider(): array
    {
        return [
            [
                ['status' => self::RESPONSE_MESSAGE_SUBSCRIBE_SUCCESS]
            ],
            [
                ['error' => self::RESPONSE_MESSAGE_FAIL_INVALID_EMAIL]
            ]
        ];
    }
}
