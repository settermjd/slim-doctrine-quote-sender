<?php

declare(strict_types=1);

use App\Handler\Subscribe\Email\EmailSubscribeRequestFormHandler;
use App\Handler\Subscribe\Email\EmailSubscribeRequestHandler;
use App\Handler\Subscribe\Mobile\MobileSubscribeRequestHandler;
use App\Handler\Unknown\Mobile\MobileUnknownRequestHandler;
use App\Handler\Unsubscribe\Email\EmailUnsubscribeRequestFormHandler;
use App\Handler\Unsubscribe\Email\EmailUnsubscribeRequestHandler;
use App\Handler\Unsubscribe\Mobile\MobileUnsubscribeRequestHandler;
use App\Handler\Webhook\Twilio\TwilioWebhookRequestMiddleware;
use DI\Container;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Helper\ContentLengthMiddleware;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

/** @var Container $container */
$container = require_once __DIR__ . '/../container.php';

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->add(new ContentLengthMiddleware());

$routeCollector = $app->getRouteCollector();
$routeCollector->setCacheFile(__DIR__ . '/../var/cache/route.cache.txt');

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

/**
 * Handle all webooks received from Twilio.
 */
$app->post('/webhook/twilio', [TwilioWebhookRequestMiddleware::class, 'handle']);

$app->group('/mobile', function (RouteCollectorProxy $group) use ($app) {
    $group->group('/request', function (RouteCollectorProxy $group) use ($app) {
        $group
            ->get('/subscribe', [MobileSubscribeRequestHandler::class, 'handle'])
            ->setName('mobile.request.subscribe');
        $group
            ->get('/unsubscribe', [MobileUnsubscribeRequestHandler::class, 'handle'])
            ->setName('mobile.request.unsubscribe');
        $group
            ->get('/unknown', [MobileUnknownRequestHandler::class, 'handle'])
            ->setName('mobile.request.unknown');
    });
});

$app->group('/email', function (RouteCollectorProxy $group) use ($app) {

    $group->group('/request', function (RouteCollectorProxy $group) use ($app) {

        // Render the form for users wanting to subscribe with their email address
        $group
            ->get('/subscribe', [EmailSubscribeRequestFormHandler::class, 'handle'])
            ->setName('email.request.subscribe.form');

        // Handle requests to sign up by email address
        $group
            ->post('/subscribe', [EmailSubscribeRequestHandler::class, 'handle'])
            ->setName('email.request.subscribe');

        // Render the form for users wanting to unsubscribe with their email address
        $group
            ->get('/unsubscribe', [EmailUnsubscribeRequestFormHandler::class, 'handle'])
            ->setName('email.request.unsubscribe.form');

        // Handle requests to unsubscribe by email address
        $group
            ->post('/unsubscribe', [EmailUnsubscribeRequestHandler::class, 'handle'])
            ->setName('email.request.unsubscribe');
    });

})
    ->addMiddleware(new FlashMessageMiddleware())
    ->addMiddleware($container->get(SessionMiddleware::class));

// Create Twig
$twig = Twig::create(__DIR__ . '/../resources/templates/', ['cache' => false]);
/** @var \Twig\Loader\FilesystemLoader $loader */
$loader = $twig->getLoader();
$loader->addPath(__DIR__ . '/../resources/templates/layout', 'layout');

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->run();
