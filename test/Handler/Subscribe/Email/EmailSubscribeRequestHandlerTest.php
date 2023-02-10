<?php

declare(strict_types=1);

namespace AppTest\Handler\Subscribe\Email;

use App\Domain\User;
use App\Handler\EmailHandlerTrait;
use App\Handler\Subscribe\Email\EmailSubscribeRequestHandler;
use App\InputFilter\EmailInputFilter;
use App\Service\UserService;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class EmailSubscribeRequestHandlerTest extends TestCase
{
    use EmailHandlerTrait;

    private FlashMessagesInterface|MockObject $flashMessage;
    private ServerRequestInterface|MockObject $request;
    private UserService|MockObject $userService;

    public function setUp(): void
    {
        $this->flashMessage = $this->createMock(FlashMessagesInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->userService = $this->createMock(UserService::class);
    }

    public function testCanSubscribeUsersByEmailAddressAndRedirectBackToTheOriginalForm()
    {
        $emailAddress = 'email-address-user@example.org';
        $user = new User(null, $emailAddress, null);

        $this->flashMessage
            ->expects($this->once())
            ->method('flash')
            ->with('status', self::RESPONSE_MESSAGE_SUBSCRIBE_SUCCESS);

        $this->userService
            ->expects($this->once())
            ->method('createWithEmailAddress')
            ->with($emailAddress)
            ->willReturn($user);

        $this->request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'email' => $emailAddress
            ]);
        $this->request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(FlashMessageMiddleware::FLASH_ATTRIBUTE)
            ->willReturn($this->flashMessage);

        $handler = new EmailSubscribeRequestHandler($this->userService, new EmailInputFilter());
        $response = $this->createMock(ResponseInterface::class);
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame('/subscribe/by-email-address', $result->getHeaderLine('location'));
    }

    public function testCanHandleInvalidFormSubmissions()
    {
        $emailAddress = '@example.org';
        $this->flashMessage
            ->expects($this->once())
            ->method('flash')
            ->with('error', self::RESPONSE_MESSAGE_FAIL_INVALID_EMAIL);

        $this->userService
            ->expects($this->never())
            ->method('createWithEmailAddress');

        $this->request
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'email' => $emailAddress
            ]);
        $this->request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(FlashMessageMiddleware::FLASH_ATTRIBUTE)
            ->willReturn($this->flashMessage);

        $handler = new EmailSubscribeRequestHandler($this->userService, new EmailInputFilter());
        $response = $this->createMock(ResponseInterface::class);
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame('/subscribe/by-email-address', $result->getHeaderLine('location'));
    }
}
