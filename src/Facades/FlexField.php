<?php

namespace Motekar\FlexField\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Motekar\FlexField\FlexField
 */
class FlexField extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Motekar\FlexField\FlexField::class;
    }
}
