<?php
/**
 * 全局数据对象
 */
namespace Resource\Facades;

use \Illuminate\Support\Facades\Facade;

class GlobalData extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'global.data';
    }
}