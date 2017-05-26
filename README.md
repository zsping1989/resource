# resource
##用途
1. 后台数据表基本的增删改查的实现

## 安装:
**要求:**

一. php >=5.6

二. 安装好composer

三. laravel/framework: 5.2.*

**步骤**
$ 1 composer require zsping1989/resource
  2 注册服务提供者  Resource\Providers\ResourceMacroServiceProvider::class,
  2 在对应的模型中添加 use Resource\Models\BaseModel;
  3 在app/Http/Controllers/Controller.php添加use Resource\Controllers\CommonController;
  4 在对应的控制器中添加 use Resource\Controllers\ResourceController;
  5 利用辅助函数快速注册资源路由  createRessorceRoute('area','AreaController');
  6 注释掉 app\Http\Kernel.php 中的 //\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
  6 访问列表页面 area/index
  7 访问列表数据 area/list
  7 访问编辑页面 area/edit
