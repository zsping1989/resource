<?php
/**
 * 通过 PhpStorm 创建.
 * 创建人: zhangshiping
 * 日期: 16-5-20
 * 时间: 下午6:21
 */

namespace Resource\Models;
use Illuminate\Support\Facades\DB;


trait BaseModel{

    /**
     * 批量替换插入
     */
    public function scopeInsertReplaceAll($query,$datas){
        $datas = collect($datas);
        if(!$datas->count()){
            return false;
        }
        //表名
        $table = config('database.connections.'.$this->getConnectionName().'.prefix').$this->getTable();
        //sql数据占位符
        $value = [];
        //字段名
        $field = collect(collect($datas->first())->keys())->map(function($item)use(&$value){
            $value[] = '?';
            return '`'.$item.'`';
        })->implode(',');
        $value = implode(',',$value);

        //sql拼装
        $sql = 'replace into '.$table.' ('.$field.') values ';

        $datas->chunk(500)->map(function($chunk)use($value,$sql){
            $values = [];
            $values_sql = [];
            $chunk->map(function($item) use(&$values_sql,&$values,$value){
                $values_sql[] = '('.$value.')';
                collect($item)->map(function($item1)use(&$values){
                    $values[] = $item1;
                });
            });
            $sql .= implode(',',$values_sql);
            return DB::connection($this->getConnectionName())->insert($sql,$values);
        });
        return true;

    }

    /**
     * 多条件筛选
     * @param $query
     * @param array $options
     * @return mixed
     */
    public function scopeOptions($query,array $options=[])
    {
        //条件筛选
        collect($options['where'])->map(function($item) use(&$query){
            if(is_null($item) || $item['val']===''){
                return ;
            }
            //值处理
            if($item['exp']=='like'){
                $val = '%'.preg_replace('/([_%\'"])/','\\\$1', $item['val']).'%';
            }else if($item['exp']=='between'){
                $val = is_string($item['val']) ? explode(' - ',$item['val']):$item['val'];
            }else if($item['exp']=='in'){
                $val = is_string($item['val']) ? explode(',',$item['val']):$item['val'];
            }else{
                $val = $item['val'];
            }
            $exp = $item['exp']; // 表达式

            if($exp=='has'){
                if($val){
                    $query->whereHas('admin');
                }else{
                    $query->whereDoesntHave('admin');
                }
                return ;
            }
            if(str_contains($item['key'],'|')){ //多字段
                $query->where(function($query)use($item,$val,$exp){
                    $keys = collect(explode('|',$item['key']));
                    $keys->map(function($key)use(&$query,$val,$exp){
                        $this->relationWhere($query,$key,$exp,$val,'or');
                    });
                });
            }else{
                $this->relationWhere($query,$item['key'],$exp,$val);
            }
        });
        //排序
        isset($options['order']) AND collect($options['order'])->each(function($item,$key) use (&$query){
            $item and $query->orderBy($key,$item);
        });
        return $query;
    }


    /**
     * 关系条件筛选
     * @param $query
     * @param $key
     * @param $exp
     * @param $val
     * @param string $condition
     */
    protected function relationWhere(&$query,$key,$exp,$val,$condition='and'){
        $relation = '';
        if(str_contains($key,'.')){
            $keys = collect(explode('.',$key));
            $key = $keys->pop();
            $relation = $keys->implode('.');
        }
        if($relation){
            if($condition=='or'){
                $query->orWhereHas($relation,function($query)use($exp,$key,$val){
                    $this->jointWhere($query,$key,$exp,$val);
                });
            }else{
                $query->whereHas($relation,function($query)use($exp,$key,$val){
                    $this->jointWhere($query,$key,$exp,$val);
                });
            }
        }else{
            $this->jointWhere($query,$key,$exp,$val,$condition);
        }
    }

    /**
     * 拼接条件
     * @param $query
     * @param $key
     * @param $exp
     * @param $val
     * @param string $condition
     */
    protected function jointWhere(&$query,$key,$exp,$val,$condition='and'){
        $whereMap = ['in','between'];
        $exps = [];
        if($condition=='or'){
            $exps[] = 'or';
        }
        if(in_array($exp,$whereMap)){
            $exps[] = 'where';
            $exps[] = $exp;
            $where = camel_case(implode('_',$exps));
            $query->$where($key,$val);
        }else{
            $exps[] = 'where';
            $where = camel_case(implode('_',$exps));
            $query->$where($key,$exp,$val);
        }
    }

    /**
     * 获取本模型数据库连接对象
     * @return mixed
     */
    public function scopeMainDB(){
        return $this->getConnection();
    }


    /**
     * 获取数据表字段信息
     * param $table
     * 返回: mixed
     */
    public function scopeGetTableInfo(){
        $table = $this->getTable();
        $connection = $this->getConnectionName() ?: config('database.default');
        $prefix = config('database.connections.'.$connection.'.prefix');
        $trueTable = $prefix.$table;

        //数据表备注信息
        $data['table_comment'] =  $this->getConnection()->select('SELECT TABLE_COMMENT FROM information_schema.`TABLES` WHERE TABLE_SCHEMA= :db_name AND TABLE_NAME = :tname',
            [
                'db_name'=>config('database.connections.'.$connection.'.database'),
                'tname'=>$trueTable
            ])[0]->TABLE_COMMENT;

        //字段信息
        $data['table_fields'] = collect($this->getConnection()->select('show full COLUMNS from `'.$trueTable.'`'))
            ->map(function($item){
                $comment = explode('@',$item->Comment);
                $item->validator = array_get($comment,'1',''); //字段验证
                $comment = explode('$',$comment[0]);
                $item->showType = ends_with($item->Field,'_at') ? 'time' : array_get($comment,'1',''); //字段显示类型
                $item->showType = in_array($item->Field,['deleted_at','left_margin','right_margin','level','remember_token']) ? 'hidden' :  $item->showType;
                $comment = explode(':',$comment[0]);
                $info = ['created_at'=>'创建时间','updated_at'=>'修改时间'];
                $item->info = isset($info[$item->Field]) ? $info[$item->Field]: $comment[0]; //字段说明
                $item->info =  $item->info ?: $item->Field;
                $comment = explode(',',array_get($comment,'1',''));
                $item->values = collect($comment)->map(function($item){
                    return explode('-',$item);
                })->pluck('1','0')->filter(function($item){
                    return $item;
                })->toArray(); //字段值
                $item->showType = (!$item->showType && $item->values) ? 'radio' : $item->showType;
                $item->showType = !$item->showType ? 'text' : $item->showType;
                return collect($item)->toArray();
            })->toArray();
        return $data;
    }

    /**
     * 获取字段显示映射信息
     * @return array
     */
    public function scopeGetFieldsMap(){
        if(!isset($this->fieldsShowMaps)){
            return [];
        }
        return $this->fieldsShowMaps;
    }




} 