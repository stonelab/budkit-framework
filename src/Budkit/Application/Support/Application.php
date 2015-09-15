<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 25/06/2014
 * Time: 20:48
 */

namespace Budkit\Application\Support;

use Budkit\Dependency;
use Budkit\Event\Event;
use Budkit\Protocol\Request;
use Exception;

abstract class Application extends Dependency\Container
{

    use Mockery;

    /**
     * Construct/Initialises the application and required resources;
     *
     * @param Request $request
     */
    public function __construct()
    {
        $this->addBaseReferenceAliases();

    }

    //Adds base aliases to container
    public function addBaseReferenceAliases()
    {
        $this->createAlias([
            'app' => 'Budkit\Application\Platform',
            'console' => 'Budkit\Application\Console',
            'auth' => 'Budkit\Authentication\Authenticator',
            'cache' => 'Budkit\Cache\Manager',
            'cookie' => 'Budkit\Request\Cookie',
            'database' => 'Budkit\Datastore\Database',
            'observer' => 'Budkit\Event\Observer',
            'file' => 'Budkit\Filesystem\File',
            'config' => 'Budkit\Parameter\Manager',
            'log' => 'Budkit\Log\Ticker',
            'mailer' => 'Budkit\Mail\Mailer',
            'paginator' => 'Budkit\Datastore\Paginator',
            'redirect' => 'Budkit\Routing\Redirector',
            'router' => 'Budkit\Routing\Router',
            'session' => 'Budkit\Session\Manager',
            'sanitize' => 'Budkit\Validation\Sanitize',
            'uri' => 'Budkit\Routing\Uri',
            'validate' => 'Budkit\Validation\Validate',
            'view' => 'Budkit\View\Display',
            'viewengine' => 'Budkit\View\Engine'
        ]);

        //Sounds and looks weired, but we need to run the same event observer
        //throughout the app, especially for registering services as below.
        $this->shareInstance($this->createInstance('observer'), 'observer');

    }


    public function registerServices($services = [])
    {

        if (empty($services)) {
            $services = $this->paths['vendor'] . "/services.json";
            //If we can't load this file, throw and error;
            if (!$this->file->exists($services)) {
                return false; //could not
            }
            //decode;
            $services = json_decode($this->file->read($services), true);
        }

        //@TODO This should be moved to the provider handling class
        foreach ($services as $callable) {

            if (!class_exists($callable)) {
                throw new Exception("Could not locate the service provider {$callable}");

                return false;
            }
            //Create an instance of the callback
            $provider = $this->createInstance($callable, [$this]);

            //Check implements Service Interface;
            if (!($provider instanceof Service)) {
                throw new Exception("{$callable} Must implement the Service Interface");

                return false;
            }

            //var_dump($provider);
            //Attach the service provider;
            $this->observer->attach($provider);

        }
        //var_dump($this->observer);
        //Trigger the register event
        $this->observer->trigger(new Event("app.register", $this));

    }


    public function initialize()
    {

        //state the application is initialized;
        //register aliases as class mocks such that static calls on mock map to instance calls;
        $this->createAliasMock(
            array_merge(
                $this->aliases, [
                    "route" => 'Budkit\Routing\Router', //this such that we can call Route::add to add router;
                    'controller' => 'Budkit\Routing\Controller',
                    'view' => 'Budkit\View\Display'
                ]
            )
        );
        //var_dump($this->observer);

        //Trigger the app initialise event;
        $this->observer->trigger(new Event("app.init", $this));
    }


    //Abstract methods
    abstract public function execute(Request $request = null);

} 