<?php
/**
 * 资源控制器
 * 通过 PhpStorm 创建.
 * 创建人: 21498
 * 日期: 2016/6/14
 * 时间: 13:58
 */

namespace Resource\Controllers;


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
        $data['maps'] = $this->bindModel()->getFieldsMap();
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
        //获取带有筛选条件的对象
        $obj = $this->getWithOptionModel();
        //指定查询字段
        $fields = $this->selectFields($this->showIndexFields);
        $fields and $obj->select(in_array($this->bindModel()->getKeyName(),$fields)?$fields:array_merge([$this->bindModel()->getKeyName()],$fields));
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
    public function getWithOptionModel()
    {
        $this->bindModel OR $this->bindModel();
        $obj = $this->bindModel->with($this->selectWithFields())
            ->options($this->getOptions());
        return $obj;
    }

    /**
     * excel导出
     */
    public function export()
    {
        ini_set('memory_limit', -1);
        Excel::create($this->bindModel()->getTable(), function ($excel) {
            $excel->sheet('score', function ($sheet) {
                $keys = toLateralKey($this->showFields);
                if (!$keys) {
                    $sheet->rows([]);
                    return;
                }
                $all_titles = $this->relationTables($this->showFields, $this->bindModel());
                $title = collect($keys)->map(function ($item) use ($all_titles) {
                    return array_get($all_titles, $item, '');
                });
                $data = collect($this->getWithOptionModel()
                    ->get())
                    ->map(function ($item) use ($keys) {
                        $item = $item->toArray();
                        $row = collect($keys)->map(function ($key) use ($item) {
                            return array_get($item, $key, '');
                        });
                        return $row;
                    })
                    ->prepend($title->toArray());
                $sheet->rows($data->toArray());
            });
        })->export('xls');
    }

    /**
     * 编辑页面
     */
    public function edit($id = null)
    {

        $data['row'] = $this->getOne($id);
        //数据字段映射信息
        $data['maps'] = $this->bindModel()->getFieldsMap();
        //增删改查URL地址
        $data['configUrl'] = $this->getConfigUrl('edit');
        return Response::returns($data); //获取一条记录
    }


    /**
     * 获取一条编辑数据
     * @param null $id
     * @return \stdClass
     */
    public function getOne($id = null)
    {
        $this->bindModel OR $this->bindModel(); //绑定模型
        if (!$id) { //没有数据返回空对象
            return $this->getDefault($this->getResourceModel());
        }
        return $this->bindModel->findOrFail($id); //没有数据抛出异常
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
        $res = $this->bindModel->destroy(Request::input('ids', []));
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
    protected function getDefault($model_name)
    {
        $model = $this->modelNamespace . $model_name;
        $model = new $model();
        return collect(array_flip($model->getFillable()))->map(function ($item) {
            return null;
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

    /**
     * 绑定模型
     * @return mixed
     */
    public function bindModel()
    {
        if (!$this->bindModel) {
            $resourceModel = $this->getModelNamespace() . $this->getResourceModel();
            $this->bindModel = new $resourceModel();
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
            'dataUrl' => 'list', //翻页url
            'editUrl' => 'edit', //编辑页面
            'destroyUrl' => 'destroy', //删除url
            'exportUrl' => 'export', //导出url
            'backUrl' => 'index' //编辑后返回url
        ])->map(function ($value) use ($main,$type) {
            return str_replace($type, $value, $main);
        });
        if ($this->checkPermission) {
            $data = $data->map(function ($value) {
                return app('user.logic')->hasPermission($value) ? $value : '';
            });
        }
        return $data;
    }

    /**
     * 新增或修改,验证规则获取
     * @return mixed
     */
    abstract protected function getValidateRule();


}