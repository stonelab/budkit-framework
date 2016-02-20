<?php

namespace Budkit\View;

use Budkit\Dependency\Container;
use Budkit\Protocol\Response;
use Budkit\View\Engine as Handler;

class Engine
{

    protected $handler = null;
    protected $response;
    protected $container;

    public function __construct(Response $response, Container $container)
    {

        $this->response = $response;
        $this->container = $container;
    }

    public function getHandler()
    {

        $format = $this->response->getContentType();
        $engineClass = 'Budkit\View\Engine\\' . ucfirst($format); //Todo use Event?

        if (isset($this->container[$engineClass])) {
            return $this->container[$engineClass];
        }

        if (class_exists($engineClass)) {
            $engine = $this->container->createInstance($engineClass);
            if ($engine instanceof Format) { //good. this ensures the handler implements a compile method
                $this->handler = $engine;
            }
        } else {
            $this->handler = $this->container->createInstance( Handler\Html::class );
        }

        $this->handler->setResponse( $this->container->response );

        return $this->handler;
    }

}