<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 25/06/2014
 * Time: 20:48
 */

namespace Budkit\Application\Support;

use Budkit\Dependency;
use Budkit\Protocol\Request;

abstract class Application extends Dependency\Container
{

    use Mockery;

    protected $aliases = array(
        'app' => 'Budkit\Application\Platform',
        'app_console' => 'Budkit\Application\Console',
        'auth' => 'Budkit\Authentication\Authenticator',
        'cache' => 'Budkit\Cache\Manager',
        'config' => 'Budkit\Config\Repository',
        'cookie' => 'Budkit\Request\Cookie',
        'database' => 'Budkit\Datastore\Database',
        'events' => 'Budkit\Events\Dispatcher',
        'files' => 'Budkit\Filestore\Manager',
        'form' => 'Budkit\Layout\Html\Form',
        'html' => 'Buidkit\Layout\Html',
        'log' => 'Budkit\Log\Ticker',
        'mailer' => 'Budkit\Mail\Mailer',
        'paginator' => 'Budkit\Datastore\Paginator',
        'redirect' => 'Budkit\Routing\Redirector',
        'route' => 'Budkit\Routing\Router',
        'session' => 'Budkit\Session\Manager',
        'sanitize' => 'Budkit\Validation\Sanitize',
        'uri' => 'Budkit\Routing\Uri',
        'validate' => 'Budkit\Validation\Validate',
        'view' => 'Budkit\View\Manager',
    );

    /**
     * Construct/Initialises the apllication and required resources;
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
        $this->createAlias($this->aliases);
    }


    public function registerService()
    {
    }

    public function initialize()
    {
        //call the boot method 
        //state the application is initialized;
        //register aliases as class mocks such that static calls on mock map to instance calls;
        $this->createAliasMock(
            array_merge(
                $this->aliases, array(
                    "Route" => "Budkit\\Routing\\Router" //this such that we can call Route::add to add router;
                )
            )
        );
    }


    //Abbstract methods
    abstract public function execute(Request $request = null);

} 