<?php

declare(strict_types=1);

use App\Handler\Subscribe\SubscribeByEmailFormHandler;
use App\Handler\Subscribe\SubscribeByEmailHandler;
use App\Handler\Subscribe\SubscribeByMobileHandler;
use App\Handler\Unsubscribe\UnsubscribeByEmailFormHandler;
use App\Handler\Unsubscribe\UnsubscribeByEmailHandler;
use App\Handler\Unsubscribe\UnsubscribeByMobileHandler;
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

$app->group('/subscribe', function (RouteCollectorProxy $group) use ($app) {

    $group
        ->post('/by-mobile-number', [SubscribeByMobileHandler::class, 'handle'])
        ->setName('subscribe-by-mobile-number');

    // Render the form for users wanting to subscribe with their email address
    $group
        ->get('/by-email-address', [SubscribeByEmailFormHandler::class, 'handle'])
        ->setName('subscribe-by-email-address-form');

    // Handle requests to sign up by email address
    $group
        ->post('/by-email-address', [SubscribeByEmailHandler::class, 'handle'])
        ->setName('subscribe-by-email-address');

})
    ->addMiddleware(new FlashMessageMiddleware())
    ->addMiddleware($container->get(SessionMiddleware::class));

$app->group('/unsubscribe', function (RouteCollectorProxy $group) use ($app)
{
    $group
        ->post('/by-mobile-number', [UnsubscribeByMobileHandler::class, 'handle'])
        ->setName('subscribe-by-mobile-number');

    // Render the form for users wanting to unsubscribe with their email address
    $group
        ->get('/by-email-address', [UnsubscribeByEmailFormHandler::class, 'handle'])
        ->setName('subscribe-by-email-address-form');

    // Handle requests to unsubscribe by email address
    $group
        ->post('/by-email-address', [UnsubscribeByEmailHandler::class, 'handle'])
        ->setName('subscribe-by-email-address');

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
