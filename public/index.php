<?php

declare(strict_types=1);

use App\Handler\Subscribe\SubscribeByEmailFormHandler;
use App\Handler\Subscribe\SubscribeByEmailHandler;
use App\Handler\Subscribe\SubscribeByMobileHandler;
use DI\Container;
use Mezzio\Flash\FlashMessageMiddleware;
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

// Create Twig
$twig = Twig::create(__DIR__ . '/../resources/templates/', ['cache' => false]);
/** @var \Twig\Loader\FilesystemLoader $loader */
$loader = $twig->getLoader();
$loader->addPath(__DIR__ . '/../resources/templates/layout', 'layout');

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

$app->run();
