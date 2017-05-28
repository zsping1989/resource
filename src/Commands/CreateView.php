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
    protected $signature = 'create:view {model}';

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

    protected function getOutputPath(){
        $this->outputPath = resource_path('views/admin/order/Edit');
    }

    /**
     * 创建控制器
     */
    protected function readyDatas(){
        $model = 'App\\'.str_replace('/','\\',$this->argument('model'));
        $this->bindModel = new $model();
        $data = $this->bindModel->getTableInfo();
        $data['path'] = str_singular($this->bindModel->getTable());
        $this->datas = $data;
    }
}
