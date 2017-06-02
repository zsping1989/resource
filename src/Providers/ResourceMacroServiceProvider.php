<?php

namespace Resource\Providers;

use Illuminate\Support\Facades\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Resource\Exceptions\CustomValidator;
use Resource\Commands\CreateController;
use Resource\Services\DataRepository;

class ResourceMacroServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @param  ResponseFactory  $factory
     * @return void
     */
    public function boot(ResponseFactory $factory)
    {
        //设置数据库配置
        config(['database.connections.schema'=>config('resource.database.connections.schema')]);
        $macro = $this;
        $factory->macro('returns', function ($value,$status=200,$view=null) use ($factory,$macro) {
            $value = collect($value);
            if(Request::input('callback')){ //jsonp
               return $factory->jsonp(Request::input('callback'),$value,$status);
            }elseif(Request::input('define')=='AMD'){ //AMD
                $macro->addData($value);
                $value = 'define([],function(){ return '.collect($value)->toJson().';});';
            }elseif(Request::input('define')=='CMD'){ //CMD
                $macro->addData($value);
                $value = 'define([],function(){ return '.collect($value)->toJson().';});';
            }elseif(Request::has('dd')){ //数据打印页面
                dd($value->toArray());
            }elseif(Request::ajax() || Request::wantsJson() || Request::has('json')){ //json
                //$macro->addData($value);
                return $factory->json($value,$status);
            }elseif(Request::has('script')){ //页面
                $value = 'var '.Request::input('script').' = '.collect($value)->toJson().';';
            }else{
                $macro->addData($value);
                return $factory->json($value,$status);
                $view1 = $view?:Route::getCurrentRoute()->getCompiled()->getStaticPrefix();
                view()->share('_view',$view1);
                view()->share('page',collect(explode('/',$view1))->filter()->implode('-'));
                return view($view?:'/layouts/home',['data'=>$value]);
            }
            return $factory->make($value,$status);
        });

        //注册自定义验证
        $this->app['validator']->resolver(function($translator, $data, $rules, $messages)
        {
            return new CustomValidator($translator, $data, $rules, $messages);
        });

        //视图模板
        $this->loadViewsFrom(__DIR__.'/../Views', 'zsping1989/resource');

        //注册创建代码命令
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateController::class,
                'Resource\Commands\CreateModel',
                'Resource\Commands\CreateMigration',
                'Resource\Commands\CreateSeed',
                'Resource\Commands\CreateView',
                'Resource\Commands\ExportModel',
                'Resource\Commands\ExportMigration',
                'Resource\Commands\ExportSeed',
            ]);
        }

        //去掉为空数据
        $rquestObj = app('request');
        $rquest = collect($rquestObj->except(['order','where']))->filter(function($item,$key)use($rquestObj){
            if(is_null($item)){
                $rquestObj->offsetUnset($key);
            }
            return !is_null($item);
        })->toArray();
        //处理关系模型数据
        collect(getRelationData($rquest))->map(function($item,$key)use($rquestObj){
            $rquestObj->offsetSet($key,$item);
        });


        $this->publishes([
            __DIR__.'/../Publishes/database/seeds' => database_path('seeds'),
            __DIR__.'/../Publishes/database/migrations' => database_path('migrations'),
            __DIR__.'/../Publishes/configs' => config_path(),
            __DIR__.'/../Views' => base_path('resources/views/vendor/zsping1989/resource')
        ]);

        //需要生成的例子
        $this->publishes([
            __DIR__.'/../Publishes/controllers' => app_path('Http/Controllers/Admin')
        ], 'example');


    }

    /**
     * 添加全局数据
     * @param $value
     */
    public function addData(&$value){
        $value['options'] = [
            'where'=>Request::input('where',new \stdClass()),
            'order'=>Request::input('order',new \stdClass())
        ];
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //返回数据存放
        $this->app->singleton('option', DataRepository::class);
    }
}
