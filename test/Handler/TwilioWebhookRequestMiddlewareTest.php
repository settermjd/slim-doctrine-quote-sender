<?php

namespace AppTest\Handler;

use App\Handler\TwilioWebhookRequestMiddleware;
use App\Handler\Subscribe\SubscribeByMobileHandler;
use App\Handler\Unsubscribe\UnsubscribeByEmailHandler;
use App\Handler\Unsubscribe\UnsubscribeByMobileHandler;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TwilioWebhookRequestMiddlewareTest extends TestCase
{
    /**
     * @param array<string,string> $parsedBody
     * @dataProvider parsedBodyDataProvider
     */
    public function testCanRedirectWhenRequestParametersAreMissing(array $parsedBody)
    {
        $middleware = new TwilioWebhookRequestMiddleware();
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn($parsedBody);

        $response = (new EmptyResponse());
        $handler = new EmptyResponse();

        $modified = $middleware->handle($serverRequest, $handler);
        $this->assertNotEquals($response, $modified);
        $this->assertTrue($modified->hasHeader('location'));
        $this->assertSame(
            sprintf(
                TwilioWebhookRequestMiddleware::REDIRECT_URL_BASE,
                TwilioWebhookRequestMiddleware::REDIRECT_TYPE_UNKNOWN
            ),
            $modified->getHeaderLine('location')
        );
        $this->assertSame(302, $modified->getStatusCode());
    }

    public static function parsedBodyDataProvider(): array
    {
        return [
            [
                [
                    'From' => '+14155552672',
                    'Body' => null,
                ]
            ],
            [
                [
                    'From' => '+14155552672',
                ]
            ],
            [
                []
            ],
        ];
    }

    /**
     * @dataProvider mobileRequestDataProvider
     */
    public function testCanRouteMobileRequestCorrectly(
        string $messageText,
        string $redirectRoute
    ) {
        $middleware = new TwilioWebhookRequestMiddleware();
        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest
            ->expects($this->once())
            ->method('getParsedBody')
            ->willReturn([
                'From' => '+14155552672',
                'Body' => $messageText,
            ]);

        $response = (new EmptyResponse());
        $handler = new EmptyResponse();

        $modified = $middleware->handle($serverRequest, $handler);
        $this->assertNotEquals($response, $modified);
        $this->assertTrue($modified->hasHeader('location'));
        $this->assertSame($redirectRoute, $modified->getHeaderLine('location'));
        $this->assertSame(302, $modified->getStatusCode());
    }

    public static function mobileRequestDataProvider(): array
    {
        return [
            [
                'subscribe',
                '/mobile/request/subscribe',
            ],
            [
                'yes',
                '/mobile/request/subscribe',
            ],
            [
                'unstop',
                '/mobile/request/subscribe',
            ],
            [
                'unsubscribe',
                '/mobile/request/unsubscribe',
            ],
            [
                'cancel',
                '/mobile/request/unsubscribe',
            ],
            [
                'end',
                '/mobile/request/unsubscribe',
            ],
            [
                'quit',
                '/mobile/request/unsubscribe',
            ],
            [
                'stopall',
                '/mobile/request/unsubscribe',
            ],
            [
                'quiter',
                '/mobile/request/unknown',
            ],
            [
                'quilting',
                '/mobile/request/unknown',
            ],
        ];
    }
}
