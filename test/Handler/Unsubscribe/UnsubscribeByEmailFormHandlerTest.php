<?php

namespace AppTest\Handler\Unsubscribe;

use App\Handler\EmailHandlerTrait;
use App\Handler\Unsubscribe\UnsubscribeByEmailFormHandler;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UnsubscribeByEmailFormHandlerTest extends TestCase
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
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with(UnsubscribeByEmailFormHandler::TEMPLATE_NAME, $this->isType('array'))
            ->willReturn('');

        $handler = new UnsubscribeByEmailFormHandler($this->renderer);
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
            ->willReturn(self::RESPONSE_MESSAGE_UNSUBSCRIBE_SUCCESS);

        $this->request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(FlashMessageMiddleware::FLASH_ATTRIBUTE)
            ->willReturn($flashMessage);

        $this->renderer = $this->createMock(TemplateRendererInterface::class);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with(
                UnsubscribeByEmailFormHandler::TEMPLATE_NAME,
                [
                    'status' => self::RESPONSE_MESSAGE_UNSUBSCRIBE_SUCCESS,
                ]
            )
            ->willReturn('');

        $handler = new UnsubscribeByEmailFormHandler($this->renderer);
        $response = $handler->handle($this->request, $this->createMock(ResponseInterface::class), []);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
