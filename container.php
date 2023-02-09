<?php

use App\Handler\Subscribe\SubscribeByEmailFormHandler;
use App\Handler\Subscribe\SubscribeByEmailHandler;
use App\Handler\Subscribe\SubscribeByMobileHandler;
use App\Handler\Unsubscribe\UnsubscribeByEmailFormHandler;
use App\Handler\Unsubscribe\UnsubscribeByEmailHandler;
use App\Handler\Unsubscribe\UnsubscribeByMobileHandler;
use App\InputFilter\EmailInputFilter;
use App\InputFilter\MobileNumberInputFilter;
use App\QuoteService;
use App\UserService;
use DI\Container;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Mezzio\Session\Ext\PhpSessionPersistenceFactory;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Session\SessionMiddlewareFactory;
use Mezzio\Session\SessionPersistenceInterface;
use Mezzio\Template\TemplateRendererInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

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

$container->set(UnsubscribeByMobileHandler::class, static function (Container $c): UnsubscribeByMobileHandler {
    /** @var UserService $userService */
    $userService = $c->get(UserService::class);
    return new UnsubscribeByMobileHandler($userService, new MobileNumberInputFilter());
});

$container->set(UnsubscribeByEmailHandler::class, static function (Container $c): UnsubscribeByEmailHandler {
    /** @var UserService $userService */
    $userService = $c->get(UserService::class);
    return new UnsubscribeByEmailHandler($userService, new EmailInputFilter());
});

$container->set(UnsubscribeByEmailFormHandler::class, static function (Container $c): UnsubscribeByEmailFormHandler {
    return new UnsubscribeByEmailFormHandler();
});

$container->set(SubscribeByEmailFormHandler::class, static function (Container $c): SubscribeByEmailFormHandler {
    return new SubscribeByEmailFormHandler();
});

$container->set(SubscribeByMobileHandler::class, static function (Container $c): SubscribeByMobileHandler {
    /** @var UserService $userService */
    $userService = $c->get(UserService::class);
    return new SubscribeByMobileHandler($userService, new MobileNumberInputFilter());
});

$container->set(SubscribeByEmailHandler::class, static function (Container $c): SubscribeByEmailHandler {
    /** @var UserService $userService */
    $userService = $c->get(UserService::class);
    return new SubscribeByEmailHandler($userService, new EmailInputFilter());
});

return $container;
