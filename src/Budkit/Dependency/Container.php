<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 21/06/2014
 * Time: 17:20
 */

namespace Budkit\Dependency;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

class Container implements ArrayAccess
{

    protected $paths;


    /**
     * Holds the container references i.e objects and their parameters
     *
     * @var array
     */
    protected $container = [];

    /**
     * Holds class reference alias map
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * Construct the container class
     *
     * @param array $container
     */
    public function __construct(Array $container = [])
    {

        $this->container = $container;
    }

    /**
     * Shares an already created instance via the container;
     *
     * @param $reference
     * @param $instance
     *
     * @return bool
     */
    public function shareInstance($instance, $reference = null)
    {

        if (!is_object($instance)) {
            //@TODO maybe throw an error?
            return false;
        }

        $concrete = get_class($instance);

        //if reference is difference from $concrete add Alias
        if (!empty($reference) && ($reference !== $concrete)) {
            $alias = $reference;
            $this->createAlias($concrete, $alias);
        }

        //just save the instance in container, no need for lambdas;
        return $this->container[$concrete] = $instance;
    }

    /**
     * Creates a map of class alias stored in the container. A class can have multiple conatiners;
     *
     * @param      $alias
     * @param null $reference
     */
    public function createAlias($reference, $alias = null)
    {
        //if reference is array look for array(alias=>reference);
        if (is_array($reference)) {
            foreach ($reference as $key => $class) {
                $this->createAlias($class, $key);
            }

            return true;
        }

        //store in this->alias as an associative array of class to an array of aliases;
        foreach ((!is_array($alias) ? [$alias] : $alias) as $key) {
            $this->aliases[$key] = $reference;
        }
    }

    /**
     * Adds an object instance
     *
     * @param      $alias
     * @param      $instance
     * @param bool $shared
     */
    public function createInstance($reference, array $parameters = [], $shared = true)
    {
        $reference = $this->mapReference($reference);

        //if we have already created this reference;
        if (isset($this[$reference])) {
            return $this[$reference];
        }

        //check if $reference is already bound and return if is instance;
        $class = new ReflectionClass($reference);

        //Check that we can instantiate this class;
        if (!$class->isInstantiable()) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not instantiable.', $reference));
        }

        //Wrap the call function in a lamda;
        $callable = $this->wrapInCallable($class, $parameters);

        //if instance is defined and is instance wrap in closure and return;
        return ($shared)
            ? $this->createSharedReference($reference, $callable)
            : $this->createReference($reference, $callable);

    }

    public function setPaths($paths)
    {
        $this->paths = $paths;
    }


    public function getPaths()
    {
        return $this->paths;
    }


    /**
     * Maps an alias to a reference;
     *
     * e.g maps 'request' to the bound Request class
     * If passed alias is the class type not in alias, returns untouched;
     *
     * @param $alias
     *
     * @return string
     */
    public function mapReference($alias)
    {
        //$alias = strval($alias);
        $reference = array_key_exists($alias, $this->aliases);

        if (array_key_exists($alias, $this->aliases)) {
            return $this->aliases[$alias];
        }

        return $alias;
    }

    /**
     * Wraps the Class in a callable which can look up dependencies automatically
     * AKA. Serview locator
     *
     * @param       $class
     * @param array $paramters
     *
     * @return callable
     */
    protected function wrapInCallable($class, array $parameters = [])
    {

        return function ($container) use ($class, $parameters) {

            //if reflection class has a constructor
            if (($constructor = $class->getConstructor()) !== null) { //will return null if no constructor

                //The container will always be passed as the last argument to the constructor;
                if (($required = $constructor->getNumberOfParameters()) > 0) { //int

                    $dependencies = $constructor->getParameters();
                    $passed = count($parameters);
                    $needfrom = ($required - $passed) > 0 ? (($passed - 1) < 0 ? 0 : $passed - 1) : $required;

                    for ($i = $needfrom; $i < $required; $i++) {
                        $parameter = $dependencies[$i]; //instance of ReflectionParamter;
                        //If this parameter has not been passed, and the default value is not defined
                        if (!$parameter->isOptional() && !$parameter->isDefaultValueAvailable()) {

                            //PHP8 fix to ReflectionClass::getClass() which is deprecated 
                            //$hintedType = $parameter->getClass();
                           
                            $hintedType = $parameter->getType() && !$parameter->getType()->isBuiltin() ? new ReflectionClass($parameter->getType()->getName())  : null;
                            //$hintedTypeName = $hintedType->getName();
                            
                            

                            //$hintedType = $reflectionException = null;
                            try {
                                //if the required class exists in the container


                                //If its not in the container, we will try to load it;
                                if (!is_null($hintedType) && $hintedType->isInstantiable()) {
                                    $hintedInstance =
                                        ($hintedType->getName() == "Budkit\\Dependency\\Container") ? $container
                                            : $container->createInstance($hintedType->getName());
                                            
                                    array_push($parameters, $hintedInstance);

                                }
                            } catch (ReflectionException $reflectionException) {

                                //print_R($hintedInstance); die;

                                throw new ReflectionException(
                                    sprintf('The %1s Class requires parameter number %2s which could not be located. %3s',
                                        $class->getName(), ($i + 1) . " of type {" . $hintedTypeName . "}", $reflectionException->getMessage())
                                );
                            }
                        }
                        //if this parameter has not been passed but we have default values
                        if ($parameter->isDefaultValueAvailable()) {
                            array_push($parameters, $parameter->getDefaultValue());
                        }
                    }
                }

                //print_r($parameters);

                return $class->newInstanceArgs($parameters);
            }

            return $class->newInstanceWithoutConstructor();
        };
    }

    /**
     * Adds a shared object instance. ,
     *
     * @param      $reference
     * @param null $alias
     * @param bool $instaniate
     */
    public function createSharedReference($reference, Closure $callable, array $parameters = [])
    {
        return $this->createReference($reference, $callable, $parameters, true);
    }

    /**
     * Adds an unistantitated reference to a class
     *
     * @param      $reference
     * @param null $alias
     */
    public function createReference($reference, Closure $callable, array $parameters = [], $shared = false)
    {
        $reference = $this->mapReference($reference);

        $this[$reference] =
            ($shared) ? static::share($callable) : static::protect($callable);

        return $this[$reference];
    }

    /**
     * Returns a closure that stores the result of the given service definition
     * for uniqueness in the scope of this instance of Pimple.
     *
     * @param callable $callable A service definition to wrap for uniqueness
     *
     * @return Closure The wrapped closure
     */
    protected static function share($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new InvalidArgumentException('Service definition is not a Closure or invokable object.');
        }

        return function ($container) use ($callable) {
            static $object;

            if (null === $object) {
                $object = $callable($container);
            }

            return $object;
        };
    }

    /**
     * Protects a callable from being interpreted as a service.
     *
     * This is useful when you want to store a callable as a parameter.
     *
     * @param callable $callable A callable to protect from being evaluated
     *
     * @return Closure The protected closure
     */
    protected static function protect($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new InvalidArgumentException('Callable is not a Closure or invokable object.');
        }

        return function ($container) use ($callable) {
            return $callable($container);
        };
    }

    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Gets the raw container reference
     *
     * @param $reference
     *
     * @return mixed
     * @throws InvalidArgumentException
     *
     */
    public function getRawReference($reference)
    {

        $reference = $this->mapReference($reference);

        if (!array_key_exists($reference, $this->container)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $reference));
        }

        return $this->container[$reference];
    }

    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same a name as an existing parameter would break your container).
     *
     * @param string $reference The unique identifier for the parameter or object
     * @param mixed $value The value of the parameter or a closure to defined an object
     */
    public function offsetSet($reference, $value)
    {
        //$reference = $this->mapReference($reference);
        //if value is callable
        //if value is not of type reference, then reference is alias and type is reference;

        $this->container[$reference] = $value;
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $reference The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or an object
     *
     * @throws InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($reference)
    {
        $reference = $this->mapReference($reference);

        if (!array_key_exists($reference, $this->container)) {
            $this->createInstance($reference, [],
                false); //if the alias exists and not in container will create callable instance on fly;
        }

        $isFactory =
            is_object($this->container[$reference]) && method_exists($this->container[$reference], '__invoke');

        return $isFactory ? $this->container[$reference]($this) : $this->container[$reference];
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $reference The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function offsetExists($reference)
    {
        $reference = $this->mapReference($reference);

        return array_key_exists($reference, $this->container);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $reference The unique identifier for the parameter or object
     */
    public function offsetUnset($reference)
    {
        $reference = $this->mapReference($alias);

        //Remove the reference from the container;
        unset($this->container[$reference]);

        //Remove its aliases;
        while (($alias = array_search($reference, $this->aliases)) !== null) {
            unset($this->aliases[$alias]);
        }

    }

    /**
     * Aliasing the offsetGet Method so we could use $this->{$alias} directly;
     *
     * @param $reference
     *
     * @return mixed
     */
    public function __get($reference)
    {
        return $this->offsetGet($reference);
    }

    /**
     * Extends an object definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string $reference The unique identifier for the object
     * @param callable $callable A service definition to extend the original
     *
     * @return Closure The wrapped closure
     *
     * @throws InvalidArgumentException if the identifier is not defined or not a service definition
     */
    public function extendReference($reference, $callable)
    {
        if (!array_key_exists($reference, $this->container)) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $reference));
        }

        if (!is_object($this->container[$reference]) || !method_exists($this->container[$reference], '__invoke')) {
            throw new InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.',
                $reference));
        }

        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new InvalidArgumentException('Extension service definition is not a Closure or invokable object.');
        }

        $factory = $this->container[$reference];

        return $this->container[$reference] = function ($c) use ($callable, $factory) {
            return $callable($factory($c), $c);
        };
    }

    /**
     * Returns all defined value names.
     *
     * @return array An array of value names
     */
    public function keys()
    {
        return array_keys($this->container);
    }
} 