<?php
/**
 * 通过 PhpStorm 创建.
 * 创建人: zhangshiping
 * 日期: 16-5-5
 * 时间: 上午10:26
 * 自定义辅助函数
 */

function getRessorceRoutes($name, $controller, array $options = []){
    //控制器默认路由注册
    $methods = collect([
        'index'=>[ //列表页面
            'route'=>'index',
            'method'=>[
                'name'=>'index',
                'type'=>'get'
            ]
        ],
        'list'=>[ //翻页json数据
            'route'=>'list',
            'method'=>[
                'name'=>'getList',
                'type'=>'get'
            ]
        ],
        'export'=>[
            'route'=>'export',
            'method'=>[
                'name'=>'export',
                'type'=>'get'
            ]
        ],
        'import'=>[
            'route'=>'import',
            'method'=>[
                'name'=>'import',
                'type'=>'post'
            ]
        ],
        'add'=>[ //添加数据
            'route'=>'edit',
            'method'=>[
                [
                    'name'=>'edit',
                    'type'=>'get'
                ],
                [
                    'name'=>'postEdit',
                    'type'=>'post'
                ]
            ]
        ],
        'edit'=>[ //编辑数据
            'route'=>'edit/{id}',
            'method'=>[
                [
                    'name'=>'edit',
                    'type'=>'get'
                ],
                [
                    'name'=>'postEdit',
                    'type'=>'post'
                ]
            ]
        ],
        'destroy'=>[ //删除数据
            'route'=>'destroy',
            'method'=>[
                'name'=>'postDestroy',
                'type'=>'post'
            ]
        ]
    ]);
    if($options){
        $except = array_get($options,'except');
        $only = array_get($options,'only');
        $except AND $methods = $methods->except($except);
        $only AND $methods = $methods->only($only);
    }
    return $methods;
}

/**
 * 创建资源路由
 * @param $name
 * @param $controller
 * @param array $options
 */
function createRessorceRoute($name, $controller, array $options = []){
    //控制器默认路由注册
    $methods = getRessorceRoutes($name, $controller,$options);
    //路由注册
    $methods->map(function($item)use($name,$controller){
        $type = array_get($item,'method.type',false);
        if($type){
            \Illuminate\Support\Facades\Route::$type($name.'/'.$item['route'],$controller.'@'.$item['method']['name']);
        }else{
            foreach($item['method'] as $row){
                $type = $row['type'];
                \Illuminate\Support\Facades\Route::$type($name.'/'.$item['route'],$controller.'@'.$row['name']);
            }
        }
    });
}

/**
 * 创建编辑路由
 * @param $route
 */
function createEditRoute($route){
    $info = explode('/',$route);
    $class = studly_case(array_get($info,0));
    $method = studly_case(array_get($info,1));
    \Illuminate\Support\Facades\Route::get($route.'/{id?}', $class.'Controller@'.lcfirst($method));
    \Illuminate\Support\Facades\Route::post($route.'/{id?}', $class.'Controller@post'.$method);
}

/**
 * 前端弹窗参数返回
 * @param array $data
 * @param int $status
 * @return array
 */
function alert($data = [],$status=200){
    //默认值
    $defult  = [
        200=>[
            'showClose'=> true, //显示关闭按钮
            'title'=> '操作成功!', //消息内容
            'message'=> '', //消息内容
            'type'=>'success', //消息类型
            'position'=>'top',
            'iconClass'=>'', //图标
            'position'=>'top', //图标
            'customClass'=>'', //自定义样式
            'duration'=>3000, //显示时间毫秒
            'show'=>true //是否自动弹出
        ],
        'other'=>[
            'showClose'=> true, //显示关闭按钮
            'title'=> '操作失败!', //消息内容
            'message'=> '', //消息内容
            'type'=>'danger', //消息类型
            'position'=>'top',
            'iconClass'=>'', //图标
            'customClass'=>'', //自定义样式
            'duration'=>3000, //显示时间毫秒
            'show'=>true //是否自动弹出
        ]
    ];
    return collect(isset($defult[$status]) ? $defult[$status] : $defult['other'])->merge($data)->toArray();
}

/**
 * 关系数据处理
 * @param $data
 * @param array $result
 * @return array
 */
function getRelationData($data,&$result = []){
    if (!is_array($data)) {
        return $data;
    }else{
        collect($data)->map(function($item,$k)use(&$result){
            if(str_contains($k,'.')){
                $keys = explode('.',$k);
                $first = $keys[0];
                unset($keys[0]);
                $result[$first][implode('.',$keys)] = $item;
            }else{
                $result[$k] = $item;
            }
        });
        foreach($result as $key=>$value){
            if(is_array($value)){
                $result[$key] = getRelationData($value);
            }
        }
    }
    return $result;
}

/**
 * 多级数组转换为一级
 * @param $data
 * @param array $result
 * @param string $key
 * @return mixed
 */
function toLateralData($data,&$result=[],$k=''){
    if (!is_array($data)) {
        return $data;
    } else {
        foreach($data as $key=>$item){
            if(!is_array($item)){
                $result[$k.$key] = $item;
            }else{
                toLateralData($item,$result,$k.$key.'.');
            }
        }
    }
    return $result;
}




