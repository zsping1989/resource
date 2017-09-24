{!! $php !!}

namespace {{$namespace}};

use Resource\Controllers\ResourceController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
@if ($model_namespace)
use {{$model}};
@else
use App\{{$model}};
@endif
use Illuminate\Support\Facades\Response;

class {{$name}}Controller extends Controller
{
    use ResourceController;

@if ($model_namespace)
    /**
     * 模型命名空间
     * @var string
     */
    protected $modelNamespace = '{{$model_namespace}}';
@endif

    /**
     * 资源模型
     * @var string
     */
    protected $resourceModel = '{{$modelName}}';

    /**
     * 验证规则
     * @return array
     */
    protected function getValidateRule(){
@if ($has_unique)
        $id = Request::input('id',0);
@endif
@foreach ($tableInfo['table_fields'] as $table_field)
@if ($table_field['showType']=='password')
        if(!Request::input('{{$table_field['Field']}}')){
            Request::offsetUnset('{{$table_field['Field']}}');
        }
@endif
@endforeach
        return [{!! $validates !!}];
    }
@if ($is_tree_model)
    /**
     * 编辑页面
     */
    public function edit($id = null)
    {
        $data['row'] = $this->getOne($id);
        //数据字段映射信息
        $data['maps'] = $this->getFieldsMap($this->editFields,$this->newBindModel());
        //查询可选择的父级角色
        $data['maps']['optional_parents'] = {{$modelName}}::optionalParent($id ? $data['row'] : null)
            ->orderBy('left_margin', 'asc')
            ->get();
        //增删改查URL地址
        $data['configUrl'] = $this->getConfigUrl('edit');
        return Response::returns($data); //获取一条记录
    }
@endif

}
