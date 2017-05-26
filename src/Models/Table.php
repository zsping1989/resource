<?php
/**
 * 后台用户表
 */

namespace Resource\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $connection='schema';
    protected $table = 'TABLES'; //数据表名称

}
