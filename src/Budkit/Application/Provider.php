<?php

namespace Budkit\Application;

use Budkit\Application\Support\Service;
use Budkit\Dependency\Container;

/**
 *
 * This class describes the [services](?file=Budkit/Application/Support/Service.php) provided to the application instance by
 * the budkit/framework package. It provides callbacks to app events. **Note:** This should always be the last called provider in services array declared in `vendor/services.json` to allow for specific route overwrites
 *
 */
final class Provider implements Service
{

    /**
     * The application Instance container
     *
     * @var Container
     */
    protected $application;

    /**
     * The class constructor.
     *
     * @param Container $application the application instance
     */
    public function __construct(Container $application)
    {
        $this->application = $application;
    }

    /**
     * Gets the package directory.
     *
     * @return string Path to the package directory
     */
    public static function  getPackageDir()
    {
        return __DIR__ . "/";
    }

    /**
     * The package app.register event callback
     *
     * - Runs Route::add to create a default base path ("home") route.
     *
     */
    public function onRegister()
    {
        $application = $this->application;
        \Route::add("/", "home", function ($response, $params = null) use ($application) {
            return $response->addContent("<pre>Welcome to Budkit.\nTo change this page add a new basepath route like so \n\n Route::add('/', function(\$route){\n   ...\n });</pre>");
        });
    }

    /**
     * Defines app events to listen to
     *
     * @return array list of app events to listen to
     */
    public function definition()
    {
        return ["app.register" => "onRegister"];
    }
}