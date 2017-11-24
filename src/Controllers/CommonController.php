<?php
/**
 * 资源控制器
 * 通过 PhpStorm 创建.
 * 创建人: 21498
 * 日期: 2016/6/14
 * 时间: 13:58
 */

namespace Resource\Controllers;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Resource\Facades\Condition;

trait CommonController{
    /**
     * 筛选条件
     * @var array
     */
    protected $sizer = [
        'id'=>'like'
    ];

    /**
     * 其它筛选条件输出
     * @var array
     */
    protected $otherSizerOutput = [];

    /**
     * 筛选条件默认值
     * @var array
     */
    protected $sizerDefault=[];

    /**
     * 默认排序
     * @var array
     */
    protected $orderDefault = [
        'created_at'=>'desc',
        'id'=>'asc'
    ];

    /**
     * 是否检查用户拥护的url权限
     * 获取Index页面的url配置地址
     * @var bool
     */
    protected $checkPermission = true;

    /**
     * Index页面字段名称显示
     * @var array
     */
    public $showIndexFields=[];

    /**
     * Index页面字段名称显示多条数据统计值
     * @var array
     */
    public $showIndexFieldsCount=[];

    /**
     * 编辑页面显示字段
     * @var array
     */
    public $editFields = [];

    /**
     * excel导出数据字段
     * @var array
     */
    public $exportFields = [];



    /**
     * 显示字段表前缀自定义
     * @var array
     */
    protected $showPrefixions = [];


    /**
     * 查询需要输出的字段信息
     * @param $data
     * @param array $result
     * @param string $k
     * @return array
     */
    public function getWithFields($data, &$result = [], $k = '')
    {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $fileds = $this->selectFields($item);
                if ($fileds) {
                    $result[$k ? $k . '.' . $key : $key] = $fileds;
                } else {
                    $result[] = $k ? $k . '.' . $key : $key;
                }
                $this->getWithFields($item, $result, $k ? $k . '.' . $key : $key);
            }
        }
        return $result;
    }

    /**
     * 查询所需字段
     * @return array
     */
    protected function selectWithFields($fields_key='showIndexFields'){
        $result = [];
        foreach($this->getWithFields($this->$fields_key) as $key=>$withField){
            if(is_array($withField) &&  array_get($this->getShowIndexFieldsCount(),$key)){
                $result[$key] = function($q)use($withField,$key){
                    $q->select($withField);
                    $withCount = collect(array_get($this->getShowIndexFieldsCount(),$key))->filter(function($item){
                        return !is_array($item);
                    })->toArray();
                    $q->withCount($withCount);
                };
            }elseif(is_array($withField)){
                $result[$key] = function($q)use($withField){
                    $q->select($withField);
                };
            }elseif(!is_numeric($key) && array_get($this->getShowIndexFieldsCount(),$key)) {
                $result[$key] = function ($q) use ($key) {
                    $withCount = collect(array_get($this->getShowIndexFieldsCount(),$key))->filter(function($item){
                        return !is_array($item);
                    })->toArray();
                    $q->withCount($withCount);
                };
            }else{
                $result[$key] = $withField;
            }
        }
        return $result;
    }

    /**
     * 查询所需字段
     * @return array
     */
    protected function selectWithEidtFields($fields_key='showIndexFields'){
        $result = [];
        foreach($this->getWithFields($this->$fields_key) as $key=>$withField){
            if(is_array($withField)){
                $result[$key] = function($q)use($withField){
                    $q->select($withField);
                };
            }else{
                $result[$key] = $withField;
            }
        }
        return $result;
    }


    /**
     * 查询字段
     * @param $fields
     * @return array
     */
    public function selectFields($fields)
    {
        $result = [];
        foreach ($fields as $field) {
            !is_array($field) and $result[] = $field;
        }
        return $result;
    }


    /**
     * 获取关联关系表字段备注信息
     * @param $fields
     * @param Model $model
     * @param array $result
     * @param string $pfix_key
     * @return array
     */
    protected function relationTables($fields,Model $model,&$result=[],$pfix_key=''){
        $result = $this->jointTitle($model,collect($this->showPrefixions)->get($pfix_key)); //字段说明值
        foreach($fields as $key=>$field){
            if(is_array($field)){
                $pfix_key1 = $pfix_key ? $pfix_key.'.'.$key : $key;
                $this->relationTables($field,$model->$key()->getRelated(),$result[$key],$pfix_key1);
            }
        }
        return $result;
    }

    /**
     * 获取字段说明
     * @param $modelName
     * @param null $comment
     * @return array
     */
    protected function jointTitle(Model $model,$comment=null){
        $data = $model->getTableInfo();
        $table_comment = array_get($data,'table_comment','');
        return collect(array_get($data,'table_fields'))
            ->pluck('info','Field')
            ->map(function($item)use($table_comment,$comment){
                if(is_null($comment)){
                    return $table_comment.$item;
                }else{
                    return $comment.$item;
                }
            })->toArray();
    }

    /**
     * 通过字段获取关联模型
     * @param $data
     * @param array $result
     * @param string $k
     * @return array
     */
    protected function getWith($data,&$result=[],$k=''){
        if (is_array($data)) {
            foreach($data as $key=>$item){
                if(is_array($item)){
                    $result[] = $k ? $k.'.'.$key :$key;
                    $this->getWith($item,$result,$k ? $k.'.'.$key :$key);
                }
            }
        }
        return $result;
    }

    /**
     * 获取筛选条件,跟排序
     * @return array
     */
    protected function getOptions(){
        $options = ['where'=>[],'order'=>[]]; //结果返回
        $input = collect(Request::only(['where','order']));
        $where = []; //输出
        $sizerDefault = collect($this->sizerDefault);
        //筛选条件全部转成一级
        collect($this->sizer)->map(function($item,$key)use(&$where,&$options,$input,$sizerDefault){
            $inputWhere = collect($input->get('where',[]));
            if(is_array($item)){
                $inputValue = $inputWhere->get($key);
                $defaultValue = $sizerDefault->get($key,[]);
                foreach($item as $k=>$row){
                    if(($val = array_get($inputValue,$k,array_get($defaultValue,$k)))!=='' && !is_null($val)){
                        $options['where'][] = [
                            'key'=>$key,
                            'exp'=>$row,
                            'val'=>$val
                        ];
                    }
                    $where[$key][$k] = ($val || $val==='0' || $val===0)?$val: '';
                }
            }else{
                if(($val = $inputWhere->get($key,$sizerDefault->get($key)))!=='' && !is_null($val)){
                    $options['where'][] = [
                        'key'=>$key,
                        'exp'=>$item,
                        'val'=>$val
                    ];
                }
                $where[$key] = ($val || $val==='0' || $val===0)?$val: '';
            }
        });

        $this->otherSizerOutput AND $where = array_merge($this->getOtherSizerOutput(),$where);
        $options['order'] = collect($input->get('order',[]))->merge($this->orderDefault)->toArray();
        Condition::set(['where'=>$where,'order'=>$options['order']]);
        return $options;
    }

    protected function getOtherSizerOutput(){
        return collect($this->otherSizerOutput)->map(function($item,$key){
            return Request::input('where.'.$key,$item);
        })->toArray();
    }

}