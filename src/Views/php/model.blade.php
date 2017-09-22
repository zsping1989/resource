{!! $php !!}
/**
 * {{$table_comment}}模型
 */
namespace {{$namespace}};
use Resource\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
@if ($tree)
use MarginTree\TreeModel;
@endif
@if ($softDeletes)
use Illuminate\Database\Eloquent\SoftDeletes;
@endif

class {{$name}} extends Model
{

    use BaseModel; //基础模型
@if ($tree)
    use TreeModel; //树状结构
@endif
@if ($softDeletes)
    use SoftDeletes; //软删除
@endif

    protected $table = '{{$table}}'; //数据表名称
@if($connection)
    protected $connection = '{{$connection}}'; //数据库连接
@endif
    //批量赋值白名单
    protected $fillable = [{!! $fillable !!}];
    //输出隐藏字段
    protected $hidden = [{!! $delete !!}];
    //日期字段
    protected $dates = [{!! $dates !!}];
    //字段值map
    protected $fieldsShowMaps = [{!! $fieldsShowMaps !!}];
    //字段默认值
    protected $fieldsDefault = [{!! $fieldsDefault !!}];

@foreach($checkboxs as $key=>$field)
    /**
     * 获取多选值
     * @param $value
     * @return array
     */
    public function {{camel_case('get_'.$field['Field'])}}Attribute($value)
    {
        $field = $this->getFieldsMap('{{$field['Field']}}')->toArray();
        unset($field[0]);
        return multiple($value,$field);
    }

    /**
     * 设置多选值
     * @param $value
     * @return array
     */
    public function {{camel_case('set_'.$field['Field'])}}Attribute($value)
    {
        $this->attributes['{{$field['Field']}}'] = multipleToNum($value);
    }
@endforeach

@foreach($passwords as $key=>$field)
    /**
     * 设置密码
     * @param  $value
     * @return  array
     */
    public function {{camel_case('set_'.$field['Field'])}}Attribute($value)
    {
        $this->attributes['{{$field['Field']}}'] = bcrypt($value);
    }
@endforeach

}
