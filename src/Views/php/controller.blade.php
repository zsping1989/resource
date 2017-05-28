{!! $php !!}

namespace {{$namespace}};

use Resource\Controllers\ResourceController;
use App\Http\Controllers\Controller;
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
        return [{!! $validates !!}];
    }


}
