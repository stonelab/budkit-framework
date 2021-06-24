<?php

namespace Budkit\Application;

use Budkit\Protocol\Request;
use Budkit\Protocol\Http;

/**
 * This class creates a Budkit application instance. It is in effect
 * a container of several objects required for handling an HTTP [Request](?file=Budkit/Protocol/Request.php)
 * and sending out a [Response](?file=Budkit\Protocol\Response.php). Additional objects can be attached
 * to a created instance using its inherited
 * [Dependency](?file=Budkit/Dependency/Container.php) methods
 *
 * *Usage: Create an instance as follows*
 *
 *     use Budkit\Application;
 *     $app = new Application\Instance();
 *
 *
 * The created `$app` instance contains references to the following ready to use objects
 *
 *  -  `$app->auth` For managing authentication [#](?file=Budkit/Authentication/Authenticate.php),
 *  -  `$app->encrypt` A Datastore encryption utility [#](?file=Budkit/Datastore/Encrypt.php),
 *  -  `$app->observer` A event management utility [#](?file=Budkit/Event/Observer.php),
 *  -  `$app->listener` Handles event callbacks [#](?file=Budkit/Event/Listener.php),
 *  -  `$app->request` Manages the request [#](?file=Budkit/Protocol/Http/Request.php),
 *  -  `$app->response` Manages the response [#](?file=Budkit/Protocol/Http/Response.php),
 *  -  `$app->sanitize` An input sanitation class [#](?file=Budkit/Validation/Sanitize.php),
 *  -  `$app->validate` A data validator [#](?file=Budkit/Validation/Validate.php),
 *  -  `$app->router` Routes a user request [#](?file=Budkit/Routing/Router.php),
 *  -  `$app->dispatcher` A Request dispatcher [#](?file=Budkit/Routing/Dispatcher.php),
 *
 *
 * ---
 *
 */
class Instance extends Support\Application
{

    /**
     * Property is deprecated
     *
     * @deprecated since after commit [#13245](http://linkToCommit)
     */
    protected $paths;


    /**
     *
     * Constructs the Application\Instance.
     *
     * - Runs [createRequestFromGlobals()](#method:createRequestFromGlobals) to capture request globals.
     * - Runs [::initialize()](?file=Budkit/Application/Support/Application.php#method:initialize) to initialize
     * a few more instance objects.
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->shareInstance($this->createRequestFromGlobals(), "request");

        //boots all registered plugins;
        $this->initialize();
    }


    /**
     * Creates an Http\Request object
     *
     * - Captures global request variables `$_ENV`, `$_SESSION`, `$_SERVER`, `$_POST`, `$_GET`, `$_COOKIE`, `$_FILES`.
     * - These global variables (except `$_POST`) are sanitized, then destroyed by the created Request object.
     *
     * *Hint: To handle non HTTP Request, fork this class and overwrite this method*
     *
     *      //1. Fork this class;
     *      use Budkit\Application;
     *      class myApplication extends Application\Instance{
     *          //Must return an object of kind Budkit/Protocol/Request
     *          protected function createRequestFromGlobals(){
     *              //Return your custom Request object
     *          }
     *      }
     *      //2. Then use as follows
     *      $app = new myApplication();
     *      $app->execute();
     *
     * @return \Budkit\Protocol\Http\Request [#](?file=Budkit/Protocol/Http/Request.php)
     */

    protected function createRequestFromGlobals()
    {
        $_ATTRIBUTES = array_merge([], isset($_SESSION) ? $_SESSION : []);

        $SERVER = $_SERVER;

        if (isset($_POST["_method"])) {
            //Hack to allow PATCH, DELETE, OPTIONS etc!
            $SERVER['REQUEST_METHOD'] = $_POST["_method"];
        }

        return new Http\Request($_GET, $_POST, $_ATTRIBUTES, $_COOKIE, $_FILES, $SERVER);
    }

    /**
     * Dispatches the request to the router;
     *
     * @param \Budkit\Protocol\Request $request [#](?file=Budkit/Protocol/Request.php)
     *
     */
    public function execute(Request $request = null)
    {
        $request = $request ?: $this->request;

        //Creates a new vanilla response this will be shared by the dispatcher
        $response = $this->shareInstance(new Http\Response('',  200, [], $request), "response");
        



        $this->dispatcher->dispatch($request, $this->response);

    }

} 