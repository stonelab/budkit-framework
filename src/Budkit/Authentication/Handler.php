<?php
/**
 * Created by PhpStorm.
 * User: livingstonefultang
 * Date: 22/09/15
 * Time: 22:56
 */

namespace Budkit\Authentication;

interface Handler
{

    public function attest(array $credentials);

}