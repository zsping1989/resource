<?php

namespace Resource\Commands;



use Resource\Models\Table;
use Illuminate\Console\Command;

class ExportModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:model
    {--connection=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将数据库的数据表转成模型对象';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //查询数据库包含的所有数据表
        $connection = $this->option('connection')?:config('database.default');
        $prefix = config('database.connections.'.$connection.'.prefix');
        $database = config('database.connections.'.$connection.'.database');
        Table::where('TABLE_SCHEMA',$database)
            ->pluck('TABLE_NAME')->map(function($item)use($prefix,$connection){
                $leng = strlen($item);
                $prefix_leng = strlen($prefix);
                $table = substr($item,$prefix_leng,$leng-$prefix_leng);
                $model = 'App\Models\\'.studly_case(str_singular($table));
                if(!class_exists($model)){ //创建model
                    \Artisan::call('create:model',[
                        'table'=>$table,
                        '--connection'=>$connection,
                        '--softDeletes'=>true
                    ]);
                }
          });
    }

}
