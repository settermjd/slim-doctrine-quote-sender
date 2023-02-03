<?php

declare(strict_types=1);

namespace AppTest\Handler\Subscribe;

use App\Domain\User;
use App\Handler\Subscribe\SubscribeByEmailHandler;
use App\InputFilter\EmailInputFilter;
use App\UserService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SubscribeByEmailHandlerTest extends TestCase
{
    private ServerRequestInterface|MockObject $request;
    private UserService|MockObject $userService;

    public function setUp(): void
    {
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->userService = $this->createMock(UserService::class);
    }

    public function testCanSubscribeUsersByEmailAddressAndRedirectBackToTheOriginalForm()
    {
        $emailAddress = 'email-address-user@example.org';
        $user = new User(null, $emailAddress, null);

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

        $handler = new SubscribeByEmailHandler($this->userService, new EmailInputFilter());
        $response = $this->createMock(ResponseInterface::class);
        $result = $handler->handle($this->request, $response, []);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame('/api/subscribe/by-email-address', $result->getHeaderLine('location'));
    }
}
