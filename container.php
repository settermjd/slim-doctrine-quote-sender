<?php

use App\Handler\Subscribe\Email\EmailSubscribeRequestFormHandler;
use App\Handler\Subscribe\Email\EmailSubscribeRequestHandler;
use App\Handler\Subscribe\Mobile\MobileSubscribeRequestHandler;
use App\Handler\Unsubscribe\Email\EmailUnsubscribeRequestFormHandler;
use App\Handler\Unsubscribe\Email\EmailUnsubscribeRequestHandler;
use App\Handler\Unsubscribe\Mobile\MobileUnsubscribeRequestHandler;
use App\InputFilter\EmailInputFilter;
use App\InputFilter\MobileNumberInputFilter;
use App\Service\QuoteService;
use App\Service\UserService;
use DI\Container;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Mezzio\Session\Ext\PhpSessionPersistenceFactory;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Session\SessionMiddlewareFactory;
use Mezzio\Session\SessionPersistenceInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Twilio\Rest\Client;

require_once __DIR__ . '/vendor/autoload.php';

$container = new Container(require __DIR__ . '/settings.php');

$container->set(EntityManager::class, static function (Container $c): EntityManager {
    /** @var array $settings */
    $settings = $c->get('settings');

    // Use the ArrayAdapter or the FilesystemAdapter depending on the value of the 'dev_mode' setting
    // You can substitute the FilesystemAdapter for any other cache you prefer from the symfony/cache library
    $cache = $settings['doctrine']['dev_mode'] ?
        DoctrineProvider::wrap(new ArrayAdapter()) :
        DoctrineProvider::wrap(new FilesystemAdapter(directory: $settings['doctrine']['cache_dir']));

    $config = Setup::createAttributeMetadataConfiguration(
        $settings['doctrine']['metadata_dirs'],
        $settings['doctrine']['dev_mode'],
        null,
        $cache
    );

    // Add any custom types to the configuration
    foreach ($settings['doctrine']['types'] as $typeName => $typeClass) {
        Type::addType($typeName, $typeClass);
    }

    return EntityManager::create($settings['doctrine']['connection'], $config);
});

$container->set(SendGrid::class, static function(Container $c): SendGrid {
    return new SendGrid($_SERVER['SENDGRID_API_KEY']);
});

$container->set(Client::class, static function(Container $c): Client {
    return new Client($_SERVER['TWILIO_ACCOUNT_SID'], $_SERVER['TWILIO_AUTH_TOKEN']);
});

$container->set(SessionPersistenceInterface::class, static function(Container $c): SessionPersistenceInterface {
    $sessionPersistence = new PhpSessionPersistenceFactory();
    return $sessionPersistence($c);
});

$container->set(SessionMiddleware::class, static function(Container $c): SessionMiddleware {
    $sessionMiddleware = new SessionMiddlewareFactory();
    return $sessionMiddleware($c);
});

$container->set(UserService::class, static function(Container $c): UserService {
    /** @var EntityManager $em */
    $em = $c->get(EntityManager::class);
    return new UserService($em);
});

$container->set(QuoteService::class, static function(Container $c): QuoteService {
    /** @var EntityManager $em */
    $em = $c->get(EntityManager::class);
    return new QuoteService($em);
});

$container->set(MobileUnsubscribeRequestHandler::class, static function (Container $c): MobileUnsubscribeRequestHandler {
    /** @var UserService $userService */
    $userService = $c->get(UserService::class);
    return new MobileUnsubscribeRequestHandler($userService, new MobileNumberInputFilter());
});

$container->set(EmailUnsubscribeRequestHandler::class, static function (Container $c): EmailUnsubscribeRequestHandler {
    /** @var UserService $userService */
    $userService = $c->get(UserService::class);
    return new EmailUnsubscribeRequestHandler($userService, new EmailInputFilter());
});

$container->set(EmailUnsubscribeRequestFormHandler::class, static function (Container $c): EmailUnsubscribeRequestFormHandler {
    return new EmailUnsubscribeRequestFormHandler();
});

$container->set(EmailSubscribeRequestFormHandler::class, static function (Container $c): EmailSubscribeRequestFormHandler {
    return new EmailSubscribeRequestFormHandler();
});

$container->set(MobileSubscribeRequestHandler::class, static function (Container $c): MobileSubscribeRequestHandler {
    /** @var UserService $userService */
    $userService = $c->get(UserService::class);
    return new MobileSubscribeRequestHandler($userService, new MobileNumberInputFilter());
});

$container->set(EmailSubscribeRequestHandler::class, static function (Container $c): EmailSubscribeRequestHandler {
    /** @var UserService $userService */
    $userService = $c->get(UserService::class);
    return new EmailSubscribeRequestHandler($userService, new EmailInputFilter());
});

return $container;
