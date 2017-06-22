<?php

namespace Resource\Commands;

use Resource\Commands\Bases\BaseCreate;
use Illuminate\Support\Facades\DB;

class CreateModel extends BaseCreate
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:model {table : The name of model}
    {--connection}
    {--tree}
    {--softDeletes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自定义模板模型生成';

    protected $type='php';
    protected $tpl = 'php/model';
    protected $baseNamespace = 'App\Models';

    protected function getOutputPath(){
        $this->outputPath = app_path('Models/'.studly_case(str_singular($this->argument('table'))));
    }

    /**
     * 获取数据表字段信息
     * param $table
     * 返回: mixed
     */
    public function getTableInfo($table,$connection){
        $prefix = config('database.connections.'.$connection.'.prefix');
        $trueTable = $prefix.$table;

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
        $name = $this->argument('table');
        $data['php'] = '<?php'; //模板代码
        $data['tree'] = $this->option('tree'); //树状结构
        $data['table'] = $name;
        $data['softDeletes'] = $this->option('softDeletes'); //软删除
        $data['namespace']  = $this->baseNamespace; //生成代码命名空间
        $data['name'] = studly_case(str_singular($name)); //模型名称
        $connection = $this->option('connection') ?: config('database.default');
        $data['connection'] = $connection==config('database.default') ? '': $connection;
        $data['tableInfo'] = $this->getTableInfo($name,$connection); //数据表信息
        $table_fields = collect($data['tableInfo']['table_fields']);
        $data['table_comment'] = $data['tableInfo']['table_comment'];
        $data['dates'] = $table_fields->filter(function($item){
            return $item['showType']=='time' || in_array($item['Field'],['deleted_at', 'created_at','updated_at']);
        })->pluck('Field')->implode("','");
        $data['dates'] = $data['dates'] ? "'".$data['dates']."'":'';
        //隐藏输出字段
        $data['delete'] = $table_fields->filter(function($item){
            return $item['showType']=='delete' || in_array($item['Field'],['deleted_at']);
        })->pluck('Field');
        //批量赋值字段
        $data['fillable'] = $table_fields->pluck('Field')->diff($data['delete']->merge([
            'level',
            'left_margin',
            'right_margin',
            'created_at',
            'updated_at'
        ])->all())->implode("','");
        $data['fillable'] = $data['fillable'] ? "'".$data['fillable']."'":'';
        $data['delete'] = $data['delete']->implode("','");
        $data['delete'] = $data['delete'] ? "'".$data['delete']."'":'';
        $data['fieldsShowMaps'] = collect($table_fields)->filter(function ($item) {
            return in_array($item['showType'], ['radio', 'checkbox','select']);
        })->keyBy('Field')->map(function ($item, $key) {
            $res = '"' . $key . '"' . '=>[' . collect($item['values'])->map(function ($value, $key) {
                    return '"' . $key . '"' . '=>"' . $value . '"';
                })->implode(',') . ']';
            return $res;
        })->implode(',');
        $data['checkboxs'] = collect($table_fields)->filter(function ($item) {
            return in_array($item['showType'], ['checkbox']);
        });
        $this->datas = $data;
    }
}
