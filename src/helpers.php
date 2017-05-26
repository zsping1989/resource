<?php
/**
 * 通过 PhpStorm 创建.
 * 创建人: zhangshiping
 * 日期: 16-5-5
 * 时间: 上午10:26
 * 自定义辅助函数
 */

/**
 * 创建资源路由
 * @param $name
 * @param $controller
 * @param array $options
 */
function createRessorceRoute($name, $controller, array $options = []){
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
            'message'=> '操作成功!', //消息内容
            'type'=>'success', //消息类型
            'iconClass'=>'', //图标
            'customClass'=>'', //自定义样式
            'duration'=>3000, //显示时间毫秒
            'show'=>true //是否自动弹出
        ],
        'other'=>[
            'showClose'=> true, //显示关闭按钮
            'message'=> '操作失败!', //消息内容
            'type'=>'error', //消息类型
            'iconClass'=>'', //图标
            'customClass'=>'', //自定义样式
            'duration'=>3000, //显示时间毫秒
            'show'=>true //是否自动弹出
        ]
    ];
    return collect(isset($defult[$status]) ? $defult[$status] : $defult['other'])->merge($data)->toArray();
}







