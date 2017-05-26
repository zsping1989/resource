<?php

namespace Resource\Providers;

use Illuminate\Support\Facades\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Resource\Exceptions\CustomValidator;

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
        //
    }
}
