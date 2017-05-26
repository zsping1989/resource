{!! $php !!}

namespace {{$namespace}};

use App\Exceptions\ResourceController;
use App\Http\Controllers\Controller;
use App\{{$model}};
use Illuminate\Support\Facades\Response;

class {{$name}}Controller extends Controller
{
    use ResourceController;

    /**
     * 验证规则
     * @return array
     */
    protected function getValidateRule(){
        return [{!! $validates !!}];
    }

    /**
     * 绑定模型
     */
    protected function bindModel(){
        $this->bindModel = new {{$modelName}}();
    }




}
