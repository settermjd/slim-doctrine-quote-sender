<?php

declare(strict_types=1);

use App\UserService;
use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

/** @var Container $container */
$container = require_once __DIR__ . '/../container.php';

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->get('/create-user', function (Request $request, Response $response, $args) {
    $emailAddress = "matthew@matthewsetter.com";
    $fullName = "Matthew Setter";

    /** @var UserService $userService */
    $userService = $this->get(UserService::class);

    $userService->create($fullName, $emailAddress);
    $response->getBody()->write("New user created!");

    return $response;
});

$app->run();
