<?php

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
| Load The Application Configuration
|--------------------------------------------------------------------------
|
| The Application routes are kept separate from the application starting
| just to keep the file a little cleaner. We'll go ahead and load in
| all of the routes now and return the application to the callers.
|
*/

$config     = require $paths['app'] . '/config.inc';
$configDir  = $paths['app'] . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR;
$configExt  = ".ini";

$app->shareInstance($app->createInstance('config',
    [$app->createInstance( Budkit\Parameter\Repository\File::class,
        [$configDir, $configExt]
    )]
), 'config');

$app->config->addParameters( $config );



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
| Share user defined paths
|--------------------------------------------------------------------------
|
*/
$app['paths'] = $paths;

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


return $app;