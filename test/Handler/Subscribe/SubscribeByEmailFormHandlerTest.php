<?php

namespace AppTest\Handler\Subscribe;

use App\Handler\EmailHandlerTrait;
use App\Handler\Subscribe\SubscribeByEmailFormHandler;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SubscribeByEmailFormHandlerTest extends TestCase
{
    use EmailHandlerTrait;

    private ServerRequestInterface|MockObject $request;

    public function setUp(): void
    {
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    public function testCanSuccessfullyHandleRequests()
    {
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer
            ->expects($this->once())
            ->method('render')
            ->with('app::subscribe-by-email', $this->isType('array'))
            ->willReturn('');

        $handler = new SubscribeByEmailFormHandler($renderer);
        $response = $handler->handle($this->request, $this->createMock(ResponseInterface::class), []);

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
                SubscribeByEmailFormHandler::TEMPLATE_NAME,
                $flashes
            )
            ->willReturn($this->createMock(ResponseInterface::class));

        $flashMessage = $this->createMock(FlashMessagesInterface::class);
        $flashMessage
            ->expects($this->once())
            ->method('getFlashes')
            ->willReturn($flashes);

        $this->request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(FlashMessageMiddleware::FLASH_ATTRIBUTE)
            ->willReturn($flashMessage);

        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer
            ->expects($this->once())
            ->method('render')
            ->with(
                'app::subscribe-by-email',
                [
                    'status' => self::RESPONSE_MESSAGE_SUBSCRIBE_SUCCESS,
                ]
            )
            ->willReturn('');

        $handler = new SubscribeByEmailFormHandler($renderer);
        $response = $handler->handle($this->request, $this->createMock(ResponseInterface::class), []);

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
