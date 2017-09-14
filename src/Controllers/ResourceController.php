<?php
/**
 * 资源控制器
 * 通过 PhpStorm 创建.
 * 创建人: 21498
 * 日期: 2016/6/14
 * 时间: 13:58
 */

namespace Resource\Controllers;


use App\Models\Admin;
use App\Models\OrderProduct;
use App\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use Resource\Facades\Condition;
use Resource\Facades\Data;

trait ResourceController
{
    /**
     * 绑定模型
     * @var
     */
    protected $bindModel;




    /**
     * 列表页面
     * @return mixed
     */
    public function index()
    {
        //查询数据结果
        $data['list'] = $this->getList();
        //数据字段映射信息
        $data['maps'] = $this->getFieldsMap($this->showIndexFields,$this->newBindModel());
        //增删改查URL地址
        $data['configUrl'] = $this->getConfigUrl();
        //条件筛选及排序返回
        $this->addOptions();
        //数据返回
        return Response::returns($data);
    }

    /**
     * 获取翻页数据
     */
    public function getList()
    {
        //指定查询字段
        $fields = $this->selectFields($this->showIndexFields);
        $fields and $this->bindModel()->select(in_array($this->newBindModel()->getKeyName(),$fields)
            ? $fields:array_merge([$this->newBindModel()->getKeyName()],$fields));

        //获取带有筛选条件的对象
        $obj = $this->getWithOptionModel();

        //获取分页数据
        $data = $obj->paginate();

        //返回响应数据存放,方便操作日志记录
        Data::set('list', $data);
        return $data;
    }




    /**
     * 获取条件拼接对象
     * @return mixed
     */
    public function getWithOptionModel($fields_key='showIndexFields')
    {
        $this->bindModel OR $this->bindModel();
        $obj = $this->bindModel->with($this->selectWithFields($fields_key))
            ->withCount(collect($this->getShowIndexFieldsCount())->filter(function($item,$key){
                return !is_array($item);
            })->toArray())
            ->options($this->getOptions());
        return $obj;
    }

    public function getFieldsMap($showFields,$model){
        $res = $model->getFieldsMap();
        foreach($showFields as $k=>$showField){
            if(is_array($showField)){
                $res[$k] = $this->getFieldsMap($showField,$model->$k()->getRelated());
            }
        }
        return $res;
    }



    /**
     * 编辑页面
     */
    public function edit($id = null)
    {
        $data['row'] = $this->getOne($id);
        //数据字段映射信息
        $data['maps'] = $this->getFieldsMap($this->editFields,$this->newBindModel());
        //增删改查URL地址
        $data['configUrl'] = $this->getConfigUrl('edit');
        //$data['configUrl']['indexUrl'] = '';
        return Response::returns($data); //获取一条记录
    }

    /**
     * 查询所需字段
     * @return array
     */
    protected function editDefaultFields($data,$model){
        $result = [];
        $defult = $this->getDefault($model);//默认值
        $fields = [];
        foreach($data as $key=>$row){
            if(is_array($row)){
                $result[$key] = $this->editDefaultFields($row,$model->$key()->getRelated());
            }else{
                $fields[] =  $row;
            }
        }
        $result1 = $fields ? collect($defult)->filter(function($item,$key)use($fields,$model){
            return in_array($key,array_merge($fields,[$model->getKeyName()]));
        })->toArray() : $defult;
        return array_merge($result1,$result);
    }


    /**
     * 获取一条编辑数据
     * @param null $id
     * @return \stdClass
     */
    public function getOne($id = null)
    {
        $this->bindModel OR $this->bindModel(); //绑定模型
        return $id ? $this->bindModel
            ->with($this->selectWithEidtFields('editFields'))->findOrFail($id) :
            $this->editDefaultFields($this->editFields,$this->newBindModel());
    }


    /**
     * 执行修改或添加
     */
    public function postEdit(\Illuminate\Http\Request $request)
    {
        $this->validate($request, $this->getValidateRule());//验证数据
        $id = $request->get('id');
        $this->bindModel OR $this->bindModel(); //绑定模型
        $data = $id ? $request->all() : $request->except('id');
        if ($id) {
            $res = $this->bindModel->find($id)->update($data);
            if ($res === false) {
                return Response::returns(['alert' => alert(['message' => '修改失败!'], 500)]);
            }
            return Response::returns(['alert' => alert(['message' => '修改成功!'])]);
        }

        //新增
        $res = $this->bindModel->create($data);
        if ($res === false) {
            return Response::returns(['alert' => alert(['message' => '新增失败!'], 500)]);
        }
        return Response::returns(['alert' => alert(['message' => '新增成功!'])]);
    }

    /**
     * 删除数据
     * @return mixed
     */
    public function postDestroy()
    {
        $this->bindModel OR $this->bindModel(); //绑定模型
        $ids = Request::input('ids', []);
        $ids = is_array($ids) ?$ids:[$ids];
        $res = $this->bindModel->whereIn('id',$ids)->delete();
        if ($res === false) {
            return Response::returns(['alert' => alert(['message' => '删除失败!'], 500)]);
        }
        return Response::returns(['alert' => alert(['message' => '删除成功!'])]);
    }

    /**
     * 获取字段默认值
     * @param $model_name
     * @return \stdClass
     */
    protected function getDefault($model)
    {
        $default = $model->getFieldsDefault();
        return collect(array_flip(array_merge([$model->getKeyName()],$model->getFillable())))->map(function ($item,$key)use($default) {
            return array_get($default,$key,null);
        })->toArray() ?: new \stdClass();
    }

    /**
     * 获取绑定的资源模型
     * @return mixed
     */
    protected function getResourceModel()
    {
        return $this->resourceModel ?: str_replace('Controller', '', class_basename(get_class()));
    }


    public function newBindModel(){
        $resourceModel = $this->getModelNamespace() . $this->getResourceModel();
        return new $resourceModel();
    }

    /**
     * 绑定模型
     * @return mixed
     */
    public function bindModel()
    {
        if (!$this->bindModel) {
            $this->bindModel = $this->newBindModel();
        }
        return $this->bindModel;
    }

    /**
     * 设置模型的命名空间
     * @return mixed
     */
    protected function getModelNamespace()
    {
        if (!isset($this->modelNamespace)) {
            $this->modelNamespace = 'App\\Models\\';
        }
        return $this->modelNamespace;
    }

    /**
     * 结果返回添加筛选条件跟排序
     */
    protected function addOptions()
    {
        Data::set('options', [
            'where' => Condition::get('where', new \stdClass()),
            'order' => Condition::get('order', new \stdClass())
        ]);
    }

    /**
     * 获取资源控制器操作地址
     * @return static
     */
    public function getConfigUrl($type='index')
    {
        $main = Route::getCurrentRoute()
            ->getCompiled()
            ->getStaticPrefix();
        $data = collect([
            'indexUrl' => ['name'=>'index','method'=>1], //编辑后返回url
            'listUrl' => ['name'=>'list','method'=>1], //翻页url
            'showUrl' => ['name'=>'edit','method'=>1], //编辑查看页面
            'exportUrl' => ['name'=>'export','method'=>1], //导出url
            'destroyUrl' => ['name'=>'destroy','method'=>2], //删除url
            'editUrl' => ['name'=>'edit','method'=>2] //执行修改或新增数据
        ])->map(function ($item) use ($main,$type) {
            $item['path'] = str_replace($type, $item['name'], $main);
            return $item;
        });
        if ($this->checkPermission) {
            $data = $data->map(function ($item) {
                $item['path'] = app('user.logic')->hasPermission($item['path'],$item['method']) ? $item['path'] : '';
                return $item;
            });
        }
        return $data->map(function($item){
            return $item['path'];
        });
    }

    /**
     * 新增或修改,验证规则获取
     * @return mixed
     */
    abstract protected function getValidateRule();

    protected function getShowIndexFieldsCount(){
        if($this->showIndexFieldsCount){
            return $this->showIndexFieldsCount;
        }
        return [];
    }

    protected function getExportFields($fields,$model){
        $res = [];
        $select_fields = $this->selectFields($fields);
        if(!$select_fields){
            $res = array_merge([$model->getKeyName()],array_merge($res,$model->getFillable()));
        }else{
            $res = in_array($model->getKeyName(),$select_fields)?$select_fields:array_merge([$model->getKeyName()],$select_fields);
        }
        foreach($fields as $key=>$field){
            if(is_array($field)){
                $res[$key] = $this->getExportFields($field,$model->$key()->getRelated());
            }
        }
        return $res;
    }


    /**
     * 导出excel数据表
     */
    public function export()
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', '0');
        $table = $this->newBindModel()->getTable(); //导出表名
        Excel::create($table, function ($excel)use($table) {
            $excel->sheet($table, function ($sheet) {
                $keys = toLateralKey($this->getExportFields($this->exportFields,$this->newBindModel()));
                if (!$keys) {
                    $sheet->rows([]);
                    return;
                }
                $all_titles = $this->relationTables($this->exportFields, $this->newBindModel());
                $title = collect($keys)->map(function ($item) use ($all_titles) {
                    return array_get($all_titles, $item, '');
                });
                $maps = $this->getFieldsMap($this->exportFields,$this->newBindModel());
                $data = collect($this->getWithOptionModel('exportFields')
                    ->get())
                    ->map(function ($item) use ($keys,$maps) {
                        $item = $item->toArray();
                        $row = collect($keys)->map(function ($key) use ($item,$maps) {
                            $value = array_get($item, $key, '');
                            $map = array_get($maps,$key);
                            if($map){
                                if(!is_array($value)){
                                    $value = array_get(array_get($maps,$key),$value);
                                }else{
                                    $value = collect($value)->map(function($value)use($map){
                                        return array_get($map,$value);
                                    })->implode(',');
                                }
                            }
                            return $value;
                        });
                        return $row;
                    })
                    ->prepend($title->toArray()) //标题
                    ->prepend($keys); //key
                $sheet->setAutoSize(true);
                $sheet->rows($data->toArray(),true);
            });
        })->export('xlsx');
    }

    /**
     * 验证导入数据
     */
    private function verifyImport(&$reader,$table=null){
        $table = $table ?: $this->newBindModel()->getTable(); //导入表名
        $verify_table = $reader->sheet(0)->getTitle();
        if($table!=$verify_table){
            dd('您选择的EXCEL文件有误,请使用规范模板导入!');
        }
    }

    /**
     * 导入数据
     */
    public function import(){
        ini_set ('memory_limit', -1);
        ini_set('max_execution_time', '0');
        //上传excel文件路径
        if(!app('request')->file('excel')){
            dd('请先选择EXCEL文件');
        }
        $filePath = app('request')->file('excel')->getRealPath();
        Excel::load($filePath, function($reader){
            $this->verifyImport($reader);
            $now = date('Y-m-d H:i:s'); //当前时间
            $maps = $this->getFieldsMap($this->exportFields,$this->newBindModel());
            $default = $this->editDefaultFields($this->exportFields,$this->newBindModel());
            $datas = $reader->all()->forget(0)->filter(function($item){ //过滤全部为空的数据
                $flog = false;
                foreach($item as $value){
                    $value and $flog=true;
                }
                return $flog;
            })->map(function($item)use($maps,$default){
                return collect($item)->map(function($item,$key)use($maps,$default){
                    $map = array_get($maps,$key);
                    if($map){
                        $value = array_get(array_flip($map),trim($item),array_get($default,$key));
                    }else{
                        $value = is_null($item) ? array_get($default,$key) : trim($item);
                    }
                    return $value;
                })->toArray();
            })->toArray(); //读取数据
            $key_name = $this->newBindModel()->getKeyName();
            $bindModel = $this->getModelNamespace() . $this->getResourceModel();
            foreach($datas as $row){
                $bindModel::updateOrCreate([$key_name=>array_get($row,$key_name)?:null],$row); //更新,创建数据
            }
            dd('处理完成');
        });
    }

}