<?php

namespace Resource\Commands;

use Resource\Commands\Bases\BaseCreate;

class CreateView extends BaseCreate
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:view {model} {template} {output?} {--namespace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '自定义模板视图生成';

    protected $type='vue';
    protected $tpl = 'html/edit';
    protected $baseNamespace = '';
    /**
     * 绑定模型
     * @var
     */
    protected $bindModel;

    /**
     * 生成代码输出位置
     */
    protected function getOutputPath(){
        if($this->argument('output')){
            $this->outputPath = resource_path('assets/js/'.$this->argument('output'));
        }else{
            $this->outputPath = resource_path('assets/js/admin/'.snake_case(basename($this->argument('model'))).'/'.studly_case($this->argument('template')));
        }
    }

    /**
     * 创建控制器
     */
    protected function readyDatas(){
        $this->tpl = 'html/'.$this->argument('template');
        $data['model_namespace'] = false;
        if($this->option('namespace')){
            $model = str_replace('/','\\',$this->argument('model'));
        }else{
            $model = 'App\\'.str_replace('/','\\',$this->argument('model'));
        }
        $this->bindModel = new $model();
        $data = $this->bindModel->getTableInfo();
        $data['path'] = str_singular($this->bindModel->getTable());
        if($this->argument('template')=='index'){
            $data['show_fields'] = collect($data['table_fields'])
                ->filter(function($item){
                    return !in_array($item['showType'],['password','hidden','delete']) && !in_array($item['Field'],['deleted_at']);
                })->keyBy('Field')->map(function($item){
                    return [
                        'name'=>$item['info'],
                        'order'=>true,
                    ];
                });

        }
        $this->datas = $data;
    }
}
