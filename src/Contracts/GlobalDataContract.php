<?php

namespace Resource\Contracts;


interface GlobalDataContract{
    /**
     * 设置页面需要返回的公共数据
     * @return mixed
     */
    public function setPageData();
}