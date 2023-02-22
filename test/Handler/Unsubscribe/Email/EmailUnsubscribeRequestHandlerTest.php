<?php

namespace AppTest\Handler\Unsubscribe\Email;

use App\Handler\EmailHandlerTrait;
use App\Handler\Unsubscribe\Email\EmailUnsubscribeRequestHandler;
use App\InputFilter\EmailInputFilter;
use App\Service\UserService;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EmailUnsubscribeRequestHandlerTest extends TestCase
{
    use EmailHandlerTrait;

    private MockObject|ServerRequestInterface $request;
    private MockObject|UserService $userService;
    private MockObject|FlashMessagesInterface $flashMessage;

    public function setUp(): void
    {
        $this->flashMessage = $this->createMock(FlashMessagesInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->userService = $this->createMock(UserService::class);
    }

    public function testCanSuccessfullyUnsubscribeUserByEmailAddress()
    {
        $emailAddress = 'user1@example.org';

        $this->flashMessage
            ->expects($this->once())
            ->method('flash')
            ->with('status', self::RESPONSE_MESSAGE_UNSUBSCRIBE_SUCCESS);

        $this->userService
            ->expects($this->once())
            ->method('removeByEmailAddress')
            ->with($emailAddress)
            ->willReturn(true);

        $this->request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'emailAddress' => $emailAddress,
            ]);
        $this->request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(FlashMessageMiddleware::FLASH_ATTRIBUTE)
            ->willReturn($this->flashMessage);

        $response = $this->createMock(ResponseInterface::class);

        $handler = new EmailUnsubscribeRequestHandler($this->userService, new EmailInputFilter());
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(self::ROUTE_UNSUBSCRIBE, $result->getHeaderLine('location'));
    }

    public function testCanHandleInvalidFormSubmissions()
    {
        $emailAddress = '@example.org';

        $this->flashMessage
            ->expects($this->once())
            ->method('flash')
            ->with('status', self::RESPONSE_MESSAGE_FAIL_INVALID_EMAIL);

        $this->userService
            ->expects($this->never())
            ->method('removeByEmailAddress');

        $this->request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'emailAddress' => $emailAddress,
            ]);
        $this->request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(FlashMessageMiddleware::FLASH_ATTRIBUTE)
            ->willReturn($this->flashMessage);

        $response = $this->createMock(ResponseInterface::class);

        $handler = new EmailUnsubscribeRequestHandler($this->userService, new EmailInputFilter());
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(self::ROUTE_UNSUBSCRIBE, $result->getHeaderLine('location'));
    }
}
