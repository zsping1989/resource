<?php
/**
 * 地区模型
 */
namespace Resource\Models;
use Illuminate\Database\Eloquent\Model;
use MarginTree\TreeModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{

    use TreeModel; //树状结构
    use SoftDeletes,BaseModel; //软删除

    //批量赋值白名单
    protected $fillable = ['id','name','status','parent_id'];
    //输出隐藏字段
    protected $hidden = ['deleted_at'];
    //日期字段
    protected $dates = ['created_at','updated_at','deleted_at'];

    /**
     * 字段值map
     * @var array
     */
    protected $fieldsShowMaps = [
        'status'=>[1=>'显示',2=>'不显示']
    ];

    /**
     * 字段默认值
     * @var array
     */
    protected $fieldsDefault = [
        'name'=>'',
        'status'=>1,
        'parent_id'=>0,
        'level'=>0,
        'left_margin'=>0,
        'right_margin'=>0
    ];

}
