<?php

namespace AppTest\Handler\Unsubscribe;

use App\Handler\EmailHandlerTrait;
use App\Handler\Unsubscribe\UnsubscribeByEmailHandler;
use App\InputFilter\EmailInputFilter;
use App\UserService;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UnsubscribeByEmailHandlerTest extends TestCase
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
                'email' => $emailAddress,
            ]);
        $this->request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(FlashMessageMiddleware::FLASH_ATTRIBUTE)
            ->willReturn($this->flashMessage);

        $response = $this->createMock(ResponseInterface::class);

        $handler = new UnsubscribeByEmailHandler($this->userService, new EmailInputFilter());
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame('/api/unsubscribe/by-email-address', $result->getHeaderLine('location'));
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
                'email' => $emailAddress,
            ]);
        $this->request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(FlashMessageMiddleware::FLASH_ATTRIBUTE)
            ->willReturn($this->flashMessage);

        $response = $this->createMock(ResponseInterface::class);

        $handler = new UnsubscribeByEmailHandler($this->userService, new EmailInputFilter());
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame('/api/unsubscribe/by-email-address', $result->getHeaderLine('location'));
    }
}
