<?php

namespace Budkit\Routing;


class Factory {
    /**
     *
     * The route class to create.
     *
     * @param string
     *
     */
    protected $class = 'Budkit\Routing\Route';

    /**
     *
     * A reusable Regex object.
     *
     * @param Regex
     *
     */
    protected $regex;

    /**
     *
     * The default route specification.
     *
     * @var array
     *
     */
    protected $spec = [
        'tokens'     => [],
        'server'     => [],
        'method'     => [],
        'accept'     => [],
        'values'     => [],
        'secure'     => null,
        'wildcard'   => null,
        'routable'   => true,
        'isMatch'    => null,
        'generate'   => null,
        'namePrefix' => null,
        'pathPrefix' => null,
    ];

    /**
     *
     * Constructor.
     *
     * @param string $class The route class to create.
     *
     */
    public function __construct($class = 'Budkit\Routing\Route') {
        $this->class = $class;
        $this->regex = new Regex;
    }

    /**
     *
     * Returns a new instance of the route class.
     *
     * @param string $path The path for the route.
     *
     * @param string $name The name for the route.
     *
     * @param array  $spec The spec for the new instance.
     *
     * @return Route
     *
     */
    public function newInstance($path, $name = null, array $spec = []) {
        $spec = array_merge($this->spec, $spec);

        //var_dump($path, $name, $spec);

        $path = $spec['pathPrefix'] . $path;

        $name = ($spec['namePrefix'] && $name)
            ? $spec['namePrefix'] . '.' . $name
            : $name;

        $class = $this->class;
        $route = new $class($path, $name, [], new Regex);
        $route->addTokens($spec['tokens']);
        $route->addServer($spec['server']);
        $route->addMethod($spec['method']);
        $route->addAccept($spec['accept']);
        $route->addValues($spec['values']);
        $route->setSecure($spec['secure']);
        $route->setWildcard($spec['wildcard']);
        $route->setRoutable($spec['routable']);
        $route->setIsMatchCallable($spec['isMatch']);
        $route->setGenerateCallable($spec['generate']);

        //var_dump($route);

        return $route;
    }
}
