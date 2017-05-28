<?php

namespace Resource\Commands;

use Resource\Commands\Bases\BaseCreate;
use Illuminate\Support\Facades\DB;

class CreateMigration extends BaseCreate
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:migration {model} {--namespace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自定义模板迁移文件生成';

    protected $type='php';
    protected $tpl = 'php/migration';
    protected $baseNamespace = '';
    /**
     * 绑定模型
     * @var
     */
    protected $bindModel;


    protected function getOutputPath(){
        $this->outputPath = database_path('migrations/'.date('Y_m_d_His').'_create_'.$this->bindModel->getTable().'_table');
}


    /**
     * 获取数据表字段信息
     * param $table
     * 返回: mixed
     */
    public function getTableInfo($table,$connection){
        $prefix = config('database.connections.'.$connection.'.prefix');
        $trueTable = $prefix.$table;
        $table_info = DB::connection($connection)->select('SHOW CREATE TABLE `'.$trueTable.'`')[0];
        foreach($table_info as $key=>$value){
            if($key=='Table'){
                $data['true_table'] = $value;
            }else{
                $data['create'] =  str_replace('CREATE TABLE `'.$trueTable.'`','',$value);
                $data['create'] = str_replace(['$'],'\\$',$data['create']);
                $data['create'] = explode('AUTO_INCREMENT=',$data['create']);
                if(array_get($data['create'],1)){
                    $end = array_get(explode('DEFAULT',$data['create'][1]),'1');
                    $data['create'] =  $data['create'][0].'AUTO_INCREMENT=0 DEFAULT'.$end;
                }else{
                    $data['create'] =  $data['create'][0];
                }
            }
        }
        return $data;
    }

    /**
     * 创建迁徙文件
     */
    protected function readyDatas(){
        $data['model_namespace'] = false;
        if($this->option('namespace')){
            $model = str_replace('/','\\',$this->argument('model'));
        }else{
            $model = 'App\\'.str_replace('/','\\',$this->argument('model'));
        }
        $this->bindModel = new $model();
        $data['php'] = '<?php';
        $data['model'] = $model;
        $data['table'] = $this->bindModel->getTable();
        $data['class'] = studly_case($data['table']);
        $data['connection'] = $this->bindModel->getConnectionName() ?: config('database.default');
        $tableInfo = $this->getTableInfo($data['table'],$data['connection']); //数据表信息
        $data['create'] = $tableInfo['create'];
        $this->datas = $data;
    }
}
