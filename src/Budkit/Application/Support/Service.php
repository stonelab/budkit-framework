<?php

namespace Budkit\Application\Support;

use Budkit\Event\Listener;

/**
 * Service delegate definition.
 *
 */
interface Service extends Listener
{

    /**
     *
     * @return The directory path as a `string`
     */
    public static function  getPackageDir();

}