# resource
##用途
1. 后台数据表基本的增删改查的实现

## 安装:
**要求:**

一. php >=5.6

二. 安装好composer

三. laravel/framework: 5.2.*

**步骤**

```
1 composer require zsping1989/resource
2 php artisan vendor:publish --tag=resource
3 php artisan vendor:publish --tag=resource-example
4 composer dump
5 php artisan migrate
6 php artisan db:seed --class=AreaTableSeeder
7 在app/Http/Controllers/Controller.php添加use Resource\Controllers\CommonController;
8 在对应的控制器中添加 use Resource\Controllers\ResourceController;
9 在对应的模型中添加 use Resource\Models\BaseModel;
10 利用辅助函数快速注册资源路由  createRessorceRoute('admin/area','Admin\AreaController');
11 注释掉 app\Http\Kernel.php 中的 //\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
12 访问列表页面 /admin/area/index
13 访问列表数据 /admin/area/list
14 访问编辑页面 /admin/area/edit
```
