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

}
