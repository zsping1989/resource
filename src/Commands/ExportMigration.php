<?php

namespace Resource\Commands;



use Resource\Models\Table;
use Illuminate\Console\Command;

class ExportMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:migration
    {--connection=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将数据库的数据表转成迁徙文件';

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
            ->where('TABLE_NAME','<>','migrations')
            ->pluck('TABLE_NAME')->map(function($item)use($prefix,$connection){
                $leng = strlen($item);
                $prefix_leng = strlen($prefix);
                $table = substr($item,$prefix_leng,$leng-$prefix_leng);
                $model = 'Models\\'.studly_case(str_singular($table));
                \Artisan::call('create:migration',['model'=>$model]);
            });
    }

}
