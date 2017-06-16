<?php

namespace Resource\Commands\Bases;

use Illuminate\Console\Command;

abstract class BaseCreate extends Command
{
    //数据
    protected $datas = [];
    //模板地址
    protected $tpl = '';

    //模板根目录
    protected $tpl_base_path = 'zsping1989/resource::';

    //生成代码类型
    protected $type='';

    //输出路径
    protected $outputPath='';

    //渲染
    protected function render(){
        return view($this->tpl_base_path.$this->tpl,$this->datas)->render();
    }

    abstract protected function getOutputPath();

    /**
     * 生成代码
     */
    protected function create(){
        $this->getOutputPath();
        $file = $this->outputPath.'.'.$this->type;
        is_dir(dirname($file)) OR mkdir(dirname($file),0755,true); //创建目录
        if(file_exists($file)){ //如果文件存在
            if(!$this->confirm('file exists,Do you cover? [y|N]')){
                $this->error('file exists,文件已经存在!');
                exit;
            }
        }
        if(file_put_contents($file, $this->render())){ //写入文件
            $this->info($file.' created successfully.');
        }else{
            $this->info($file.' created  failed.');
        };
        app('composer')->dumpAutoloads(); //自动加载文件
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->readyDatas();
        $this->datas['startSymbol']='{{';
        $this->datas['endSymbol']='}}';
        $this->create();
    }

    /**
     * 准备数据
     * @return mixed
     */
    abstract protected function readyDatas();

}
