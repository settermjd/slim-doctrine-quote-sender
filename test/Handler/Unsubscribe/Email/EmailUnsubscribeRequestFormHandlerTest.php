<?php

namespace AppTest\Handler\Unsubscribe\Email;

use App\Handler\EmailHandlerTrait;
use App\Handler\Unsubscribe\Email\EmailUnsubscribeRequestFormHandler;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class EmailUnsubscribeRequestFormHandlerTest extends TestCase
{
    use EmailHandlerTrait;

    private MockObject|TemplateRendererInterface $renderer;
    private ServerRequestInterface|MockObject $request;

    public function setUp(): void
    {
        $this->renderer = $this->createMock(TemplateRendererInterface::class);
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
                EmailUnsubscribeRequestFormHandler::TEMPLATE_NAME,
                $this->isType('array')
            )
            ->willReturn($this->createMock(ResponseInterface::class));

        $flashMessage = $this->createMock(FlashMessagesInterface::class);
        $flashMessage
            ->expects($this->once())
            ->method('getFlashes')
            ->willReturn(
                ['status' => self::RESPONSE_MESSAGE_UNSUBSCRIBE_SUCCESS]
            );

        $this->request
            ->expects($this->exactly(2))
            ->method('getAttribute')
            ->willReturnOnConsecutiveCalls($twig, $flashMessage);

        $handler = new EmailUnsubscribeRequestFormHandler();
        $response = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(ResponseInterface::class, $response);
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
                EmailUnsubscribeRequestFormHandler::TEMPLATE_NAME,
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

        $handler = new EmailUnsubscribeRequestFormHandler();
        $result = $handler->handle($this->request, $this->createMock(ResponseInterface::class), []);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    public static function flashMessageProvider(): array
    {
        return [
            [
                [
                    'status' => self::RESPONSE_MESSAGE_UNSUBSCRIBE_SUCCESS,
                    'action_route' => self::ROUTE_UNSUBSCRIBE,
                ]
            ],
            [
                [
                    'error' => self::RESPONSE_MESSAGE_FAIL_INVALID_EMAIL,
                    'action_route' => self::ROUTE_UNSUBSCRIBE,
                ]
            ]
        ];
    }
}
