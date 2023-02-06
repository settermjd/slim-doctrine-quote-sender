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

    public function testWillRenderFlashMessageIfMessageIsAvailable()
    {
        $flashMessage = $this->createMock(FlashMessagesInterface::class);
        $flashMessage
            ->expects($this->exactly(2))
            ->method('getFlash')
            ->with('status')
            ->willReturn(self::RESPONSE_MESSAGE_SUBSCRIBE_SUCCESS);

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

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
