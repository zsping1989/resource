<?php

namespace Resource\Commands;

use Resource\Commands\Bases\BaseCreate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateController extends BaseCreate
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:controller {name} {model?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自定义模板控制器生成';

    protected $type='php';
    protected $tpl = 'create/controller';
    protected $baseNamespace = 'App\Http\Controllers';

    protected function getOutputPath(){
        $this->outputPath = app_path('Http/Controllers/'.studly_case($this->argument('name')).'Controller');
    }

    protected function defaultModel(){
        return 'Models\\'.studly_case(basename($this->argument('name')));
    }

    /**
     * 获取数据表字段信息
     * param $table
     * 返回: mixed
     */
    public function getTableInfo(Model $tableModel){
        $connection = $tableModel->getConnectionName() ? : config('database.default');
        $prefix = $tableModel->getConnection()->getTablePrefix();
        $trueTable = $prefix.$tableModel->getTable();

        //数据表备注信息
        $data['table_comment'] =  DB::connection($connection)->select('SELECT TABLE_COMMENT FROM information_schema.`TABLES` WHERE TABLE_SCHEMA= :db_name AND TABLE_NAME = :tname',
            [
                'db_name'=>config('database.connections.'.$connection.'.database'),
                'tname'=>$trueTable
            ])[0]->TABLE_COMMENT;

        //字段信息
        $data['table_fields'] = collect(DB::connection($connection)->select('show full COLUMNS from `'.$trueTable.'`'))
            ->map(function($item){
                $comment = explode('@',$item->Comment);
                $item->validator = array_get($comment,'1',''); //字段验证
                $comment = explode('$',$comment[0]);
                $item->showType = in_array($item->Field,['created_at','updated_at']) ? 'time' : array_get($comment,'1',''); //字段显示类型
                $item->showType = in_array($item->Field,['deleted_at','left_margin','right_margin','level','remember_token']) ? 'hidden' :  $item->showType;
                $comment = explode(':',$comment[0]);
                $info = ['created_at'=>'创建时间','updated_at'=>'修改时间'];
                $item->info = isset($info[$item->Field]) ? $info[$item->Field]: $comment[0]; //字段说明
                $item->info =  $item->info ?: $item->Field;
                $comment = explode(',',array_get($comment,'1',''));
                $item->values = collect($comment)->map(function($item){
                    return explode('-',$item);
                })->pluck('1','0')->filter(function($item){
                    return $item;
                })->toArray(); //字段值
                $item->showType = (!$item->showType && $item->values) ? 'radio' : $item->showType;
                $item->showType = !$item->showType ? 'text' : $item->showType;
                return collect($item)->toArray();
            })->toArray();
        return $data;
    }

    /**
     * 创建控制器
     */
    protected function readyDatas(){
        $name = $this->argument('name');
        $data['php'] = '<?php'; //模板代码
        $data['namespace']  = dirname($name)=='.'? $this->baseNamespace : $this->baseNamespace.'\\'.studly_case(dirname($name)); //生成代码命名空间
        $data['name'] = basename($name); //控制器名称
        $data['model'] = str_replace('/','\\',$this->argument('model')) ?: $this->defaultModel(); //绑定模型名称
        $data['modelName'] = basename($data['model']); //模型名字
        $modelName = '\\App\\'.$data['model'];
        $model = new $modelName();
        $data['tableInfo'] = $this->getTableInfo($model); //数据表信息
        $data['validates'] = collect($data['tableInfo']['table_fields'])->map(function($item){
            if($item['validator']){
                return "'".$item['Field']."'=>'".$item['validator']."'";
            }
        })->filter(function($item){
            return $item;
        })->implode(",");
        $this->datas = $data;
    }
}
