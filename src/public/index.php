<?php
// print_r(apache_get_modules());
// echo "<pre>"; print_r($_SERVER); die;
// $_SERVER["REQUEST_URI"] = str_replace("/phalt/","/",$_SERVER["REQUEST_URI"]);
// $_GET["_url"] = "/";
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\Stream as ls;
use Phalcon\Events\Manager as EventsManager;
use App\Components\Locale;

use Phalcon\Cache;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Storage\SerializerFactory;

use Phalcon\Cache\Adapter\Stream as cc;

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

$loader->registerNamespaces(
    [
        'App\Components' => APP_PATH . '/component',
        'App\Listeners' => APP_PATH . '/listeners'
    ]
);

$loader->register();

$container = new FactoryDefault();
$eventsManager = new EventsManager();


$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'cache',
    function () {
        $serializerFactory = new SerializerFactory();

        $options = [
            'defaultSerializer' => 'Json',
            'lifetime'          => 7200,
            'storageDir'        => APP_PATH . '/cache',
        ];

        $adapter = new cc($serializerFactory, $options);
        return $adapter;
    }
);

$container->set(
    'locale',
    (new Locale()
    )->getTranslator()
);
$container->set(
    'mainLogger',
    function () {
        $adapter = new ls('../app/logs/main.log');
        $logger  = new Logger(
            'messages',
            [
                'mainLogger' => $adapter,
            ]

        );
        return $logger;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

$application = new Application($container);



$container->set(
    'db',
    function () {
        return new Mysql(
            [
                'host'     => 'mysql-server',
                'username' => 'root',
                'password' => 'secret',
                'dbname'   => 'db',
            ]
        );
    }
);



$eventsManager->attach(
    'notifications',
    new App\Listeners\NotificationsListeners()
);

$eventsManager->attach(
    'application:beforeHandleRequest',
    new App\Listeners\NotificationsListeners()
);

$container->set(
    'EventsManager',
    $eventsManager
);

$application->setEventsManager($eventsManager);


// $container->set(
//     'mongo',
//     function () {
//         $mongo = new MongoClient();

//         return $mongo->selectDB('phalt');
//     },
//     true
// );

try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
