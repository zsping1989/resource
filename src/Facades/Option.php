<?php

namespace Resource\Facades;

use \Illuminate\Support\Facades\Facade;

class Data extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'option';
    }
}