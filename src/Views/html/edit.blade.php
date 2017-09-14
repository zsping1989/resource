<template>
    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Quick Example</h3>
                    </div>
                    <div class="box-body">
                        <edit :ftx-data="row" :ftx-config="config">
                            <template scope="props">
                                <div class="col-lg-4 col-md-6 col-sm-12">
                                    @foreach ($table_fields as $table_field)
                                        @if($table_field['showType']=='hidden' || in_array($table_field['Field'],['id','created_at','updated_at']))
                                            @continue
                                        @endif
                                        <div :class="{'has-error':props['error']['{{$table_field['Field']}}']}" class="form-group" >
                                            <label>{{$table_field['info']}}</label>
                                             <span class="help-block">
                                                <i :class="props['error']['{{$table_field['Field']}}']?'fa-times-circle-o':'fa-info-circle'" class="fa" ></i>
                                                <span v-show="!props['error']['{{$table_field['Field']}}']">提示信息</span>
                                                <span v-for="error in props['error']['{{$table_field['Field']}}']">@{{error}}</span>
                                            </span>
                                            @if($table_field['showType']=='textarea')
                                                <textarea v-model="props['row']['{{$table_field['Field']}}']" placeholder="请输入{{$table_field['info']}}" rows="3" class="form-control"  :disabled="!props.config['dataUrl']"></textarea>
                                            @elseif($table_field['showType']=='date')
                                                <div>
                                                    <el-date-picker v-model="props['row']['{{$table_field['Field']}}']" @change="props['row']['{{$table_field['Field']}}'] = arguments[0]" placeholder="选择日期" type="date" :clearable="false" :editable="false"  :disabled="!props.config['dataUrl']">
                                                    </el-date-picker>
                                                </div>
                                            @elseif($table_field['showType']=='checkbox')
                                                <div>
                                                    <span v-for="(item,index) in maps['{{$table_field['Field']}}']">
                                                        <input v-model="props['row']['{{$table_field['Field']}}']" :value="index" type="checkbox"  :disabled="!props.config['dataUrl']"> @{{item}}
                                                    </span>
                                                </div>
                                            @elseif($table_field['showType']=='select')
                                                <select v-model="props['row']['{{$table_field['Field']}}']" class="form-control"  :disabled="!props.config['dataUrl']">
                                                    <option :value="null">请选择</option>
                                                    <option v-for="(item,index) in maps['{{$table_field['Field']}}']" :value="index">@{{item}}</option>
                                                </select>
                                            @elseif($table_field['showType']=='radio')
                                                <div class="form-radio">
                                                    <span v-for="(item,index) in maps['{{$table_field['Field']}}']">
                                                        <input v-model="props['row']['{{$table_field['Field']}}']" :value="index" type="radio"  :disabled="!props.config['dataUrl']"> @{{item}}
                                                    </span>
                                                </div>
                                            @elseif($table_field['showType']=='email')
                                                <input v-model="props['row']['{{$table_field['Field']}}']" placeholder="请输入{{$table_field['info']}}" type="email" class="form-control"  :disabled="!props.config['dataUrl']">
                                            @elseif($table_field['showType']=='password')
                                                <input v-model="props['row']['{{$table_field['Field']}}']" placeholder="请输入{{$table_field['info']}}"  type="password" class="form-control"  :disabled="!props.config['dataUrl']">
                                            @else
                                                <input v-model="props['row']['{{$table_field['Field']}}']" placeholder="请输入{{$table_field['info']}}" class="form-control" type="text"  :disabled="!props.config['dataUrl']">
                                            @endif

                                        </div>
                                    @endforeach
                                </div>
                            </template>

                        </edit>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
    export default {
        components: {
        },
        data() {
            var data = this.$store.state;
            data.config = {
                dataUrl: data.configUrl.editUrl, //数据提交地址
                backUrl: data.configUrl.indexUrl //数据列表页面
            };
            return data;
        },
        mounted() {

        }
    }
</script>