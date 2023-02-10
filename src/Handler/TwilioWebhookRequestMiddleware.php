<?php

declare(strict_types=1);

namespace App\Handler;

use App\Handler\Subscribe\SubscribeByMobileHandler;
use App\Handler\Unsubscribe\UnsubscribeByMobileHandler;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TwilioWebhookRequestMiddleware
{
    const REDIRECT_TYPE_SUBSCRIBE = 'subscribe';
    const REDIRECT_TYPE_UNKNOWN = 'unknown';
    const REDIRECT_TYPE_UNSUBSCRIBE = 'unsubscribe';
    const REDIRECT_URL_BASE = '/mobile/request/%s';

    public function handle(ServerRequestInterface $request, ResponseInterface $handler): ResponseInterface
    {
        $params = $request->getParsedBody();

        if (empty($params['Body'])) {
           return new RedirectResponse(
               sprintf(self::REDIRECT_URL_BASE, self::REDIRECT_TYPE_UNKNOWN)
           );
        }

        return match ($this->getRedirectType($params['Body'])) {
            self::REDIRECT_TYPE_SUBSCRIBE => $this->getResponse(
                $handler,
                $request,
                self::REDIRECT_TYPE_SUBSCRIBE
            ),
            self::REDIRECT_TYPE_UNSUBSCRIBE => $this->getResponse(
                $handler,
                $request,
                self::REDIRECT_TYPE_UNSUBSCRIBE
            ),
            default => $this->getResponse($handler, $request, self::REDIRECT_TYPE_UNKNOWN),
        };
    }

    private static function getRedirectType(string $keyword): string
    {
        $keyword = strtolower($keyword);

        if (in_array($keyword, UnsubscribeByMobileHandler::KEYWORDS)) {
            return self::REDIRECT_TYPE_UNSUBSCRIBE;
        }

        if (in_array($keyword, SubscribeByMobileHandler::KEYWORDS)) {
            return self::REDIRECT_TYPE_SUBSCRIBE;
        }

        return self::REDIRECT_TYPE_UNKNOWN;
    }

    public function getResponse(
        ResponseInterface $handler,
        ServerRequestInterface $request,
        string $redirectRouteSuffix
    ): ResponseInterface {
        return $handler
            ->withHeader(
                'location',
                sprintf(self::REDIRECT_URL_BASE, $redirectRouteSuffix)
            )
            ->withStatus(302);
    }
}