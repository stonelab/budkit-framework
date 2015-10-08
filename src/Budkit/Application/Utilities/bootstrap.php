<?php

/*
|--------------------------------------------------------------------------
| Important platform constants
|--------------------------------------------------------------------------
|
|
*/
require_once 'constants.php';

/*
|--------------------------------------------------------------------------
| Composer autoload classes
|--------------------------------------------------------------------------
|
| The composer auto-load class can be used to add lookup directories for
| custom application classes. This class can be assessed from the app
| controller using $app->loader.
|
| Alternatively you may use Budkit/Utitlity/Loader.
*/
$loader = require $paths['vendor'] . '/autoload.php';


/*
|--------------------------------------------------------------------------
| Setsthe default timezone
|--------------------------------------------------------------------------
|
| The composer auto-load class can be used to add lookup directories for
| custom application classes. This class can be assessed from the app
| controller using $app->loader.
|
| Alternatively you may use Budkit/Utitlity/Loader.
*/
Budkit\Helper\Date::setDefaultTimeZone();
/*
|--------------------------------------------------------------------------
| Register the exception Handler;
|--------------------------------------------------------------------------
|
| Whoops is beautiful!
|
*/
$whoops = new Whoops\Run;

$whoops->pushHandler(new Whoops\Handler\PrettyPageHandler);
$whoops->register();

/*
|--------------------------------------------------------------------------
| Create the app
|--------------------------------------------------------------------------
|
| The Application or Platform is an Dependency container for all the loaded
| Classes and Aliases required for processing a given request.
|
*/
$app = new Budkit\Application\Platform;

/*
|--------------------------------------------------------------------------
| Share user defined paths
|--------------------------------------------------------------------------
|
*/
$app->setPaths($paths);


/*
|--------------------------------------------------------------------------
| Load The Application Configuration
|--------------------------------------------------------------------------
|
| The Application routes are kept separate from the application starting
| just to keep the file a little cleaner. We'll go ahead and load in
| all of the routes now and return the application to the callers.
|
*/

$config = require $paths['config'] . '/config.inc';
$configExt = ".ini";

$app->shareInstance($app->createInstance('config',
    [$app->createInstance(Budkit\Parameter\Repository\File::class,
        [$paths['config'], $configExt]
    )]
), 'config');

$app->config->addParameters($config);


/*
|--------------------------------------------------------------------------
| Encryptor Instance
|--------------------------------------------------------------------------
|
| This will set the user defined encryption key as the global encryption
| salt, whenever the Budkit\Datastor\Encrypt class is used
|
*/

$app->createInstance("encrypt",
    [
        $app->config->get("setup.encrypt"), //get the encryption key
    ]
);


/*
|--------------------------------------------------------------------------
| Database instance
|--------------------------------------------------------------------------
|
| This will allow third parties app creators to autoload their own classes
|
*/
//@TODO check if installed before loading.
if ($app->config->get("setup.database.installed")) {

    $app->createInstance("database",
        [
            $app->config->get("setup.database.driver"), //get the database driver
            $app->config->get("setup.database") //get all the database options and pass to the driver
        ]
    );
}

/*
|--------------------------------------------------------------------------
| Session Handler
|--------------------------------------------------------------------------
|
| This will allow third parties app creators to autoload their own classes
|
*/
$app->shareInstance(
    $app->createInstance("session",
        [
            $app->config->get("setup.session"), //get the session vars
            $app
        ]
    ),
    "session"
);
$app->session->start();


/*
|--------------------------------------------------------------------------
| Share the composer loader
|--------------------------------------------------------------------------
|
| This will allow third parties app creators to autoload their own classes
|
*/
$app->shareInstance($loader, 'loader');


/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| The Application routes are kept separate from the application starting
| just to keep the file a little cleaner. We'll go ahead and load in
| all of the routes now and return the application to the callers.
|
*/

$routes = $paths['app'] . '/routes.php';

if (file_exists($routes)) require $routes;

//Load vendor package routes;
//var_dump($app->loader->getPrefixes());

/*
|--------------------------------------------------------------------------
| Register App Services
|--------------------------------------------------------------------------
|
| All services in the storage/services.json will be registered.
|
*/
$app->registerServices();


/*
|--------------------------------------------------------------------------
| Bind The Application In The Container
|--------------------------------------------------------------------------
|
| This may look strange, but we actually want to bind the app into itself
| in case we need to Facade test an application. This will allow us to
| resolve the "platform" key out of this container for this app's facade.
|
*/
$app->shareInstance('platform', $app);


//print_r($request);
//$app->shareInstance( $app->createInstance( Budkit\Routing\Dispatcher::class  ), 'dispatcher');


/*
|--------------------------------------------------------------------------
| Important platform constants
|--------------------------------------------------------------------------
|
|
*/
require_once 'functions.php';


return $app;
