<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAreasTable extends Migration
{

    protected $bindModel='App\Models\Area';

    /**
     * Run the migrations.
     *
     * 返回: void
     */
    public function up()
    {
        $model = new $this->bindModel();
        $prefix = $model->getConnection()->getTablePrefix();
        $connection = $model->getConnectionName()?: config('database.default');
        DB::connection($connection)->statement("CREATE TABLE `".$prefix.$model->getTable()."` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '区域ID',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '名称@required',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态:1-显示,2-不显示\$radio@in:1,2',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父ID@sometimes|required|exists:areas,id',
  `level` smallint(6) NOT NULL DEFAULT '0' COMMENT '层级',
  `left_margin` int(11) NOT NULL DEFAULT '0' COMMENT '左边界',
  `right_margin` int(11) NOT NULL DEFAULT '0' COMMENT '右边界',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='地区'");
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        $model = new $this->bindModel();
        $prefix = $model->getConnection()->getTablePrefix();
        $connection = $model->getConnectionName()?: config('database.default');
        DB::connection($connection)->statement('drop table `'.$prefix.$model->getTable().'`;');
    }
}
