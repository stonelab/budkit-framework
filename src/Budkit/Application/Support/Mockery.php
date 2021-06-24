<?php

namespace Budkit\Application\Support;

use ReflectionClass;
use ReflectionException;

/**
 * This class adds mocking capabilities to any dependency container
 *
 * *Usage*
 *
 *      use Budkit\Dependency\Container;
 *      use Budkit\Application\Support\Mockery;
 *
 *      class MyApplication extends Container{
 *          use Mockery;
 *      }
 *
 * Objects to be mocked must implement the Budkit\Application\Support\Mockable interface
 *
 * @see [Budkit\Application\Support\Mockable](?file=Budkit/Application/Support/Mockable.php)
 *
 */
trait Mockery
{

    protected static $mockableInterface = Mockable::class;

    protected static $mockableTrait = Mock::class;


    /**
     * Exposes the ability to change the mockable Interface
     *
     * @param $interface Mockable
     *
     */
    public function setMockableInterface(Mockable $interface)
    {
        static::$mockableInterface = $interface;
    }

    /**
     * Exposes an ability to change the mock trait
     *
     * @param $trait Mock
     */
    public function setMockableTrait(Mock $trait)
    {
        static::$mockableTrait = $trait;
    }

    /**
     * Create a mock of the container object
     *
     * @$alias can also take an array or mocks e.g `[ ["MockName"=>MockableClass::class], ...]`
     * @$original  If a single ** *$alias* ** parameter is passed provide the original class name here e.g. `MockableClass::class`
     * @$autoload bool|true $autoload If you will like these mocked classes to be loaded after the containers initiation events
     *
     * @throws Exception if any passed class is not mockable i.e. does not implement the Mockable interface
     */
    public function createAliasMock($alias, $original = null, $autoload = true)
    {

        //If we are passing a large array;
        if (is_array($alias)) {
            foreach ($alias as $mock => $original) {
                $this->createAliasMock($mock, $original);
            }

            return;
        }

        //If the original class is mockable?
        try {
            $mockable = new ReflectionClass($original);
            if ($mockable->implementsInterface(static::$mockableInterface)) {
                //All mockable classes must use the MockTrait!
                $traits = $mockable->getTraits();

                if (!array_key_exists(static::$mockableTrait, $traits)) {
                    //@TODO throw an exception saying there is no mock;
                    throw new \Exception("Mocked class for alias '{$alias}'=>'{$original}' must use the trait 'Budkit\\Application\\Mock'");

                    return;
                }
                //The MockClass Name
                $class = $alias = ucfirst($alias);

                if ($mockable->isInstantiable()) {
                    if (!class_exists($class)) {

                        class_alias($original, $class);

                        //Define the original mocking container;
                        return $class::resolveOriginalClass($this, $original);
                    }
                }
            }
        } catch (ReflectionException $E) {
            //Do nothing; //@TODO maybe log to class saying not mockable;
        }
    }
} 