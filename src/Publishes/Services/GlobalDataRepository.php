<?php

namespace App\Services;


use Resource\Contracts\GlobalDataContract;
use Resource\Facades\Data;

class GlobalDataRepository implements GlobalDataContract{
    /**
     * 设置页面需要返回的公共数据
     */
    public function setPageData()
    {
        Data::set('global',[]);
    }
}