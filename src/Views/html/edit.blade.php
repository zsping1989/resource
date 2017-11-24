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
                                <?php $table_fields_show = collect($table_fields)->filter(function($item){
                                    return !(in_array($item['showType'],['hidden']) || in_array($item['Field'],['id','created_at','updated_at']));
                                }); ?>
                                @foreach ($table_fields_show->chunk(ceil($table_fields_show->count()/3)) as $table_field_chunk)
                                        <div class="col-lg-4 col-md-6 col-sm-12">
                                            @foreach ($table_field_chunk as $table_field)
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
                                                    @elseif($table_field['showType']=='month')
                                                        <div>
                                                            <el-date-picker v-model="props['row']['{{$table_field['Field']}}']" @change="props['row']['{{$table_field['Field']}}'] = arguments[0]"  format="yyyy-MM-01" placeholder="选择月份" type="month" :clearable="false" :editable="false"  :disabled="!props.config['dataUrl']">
                                                            </el-date-picker>
                                                        </div>
                                                    @elseif($table_field['showType']=='checkbox')
                                                        <div class="input-checkbox">
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
                                                    @elseif($table_field['showType']=='ztree')
                                                        <ztree v-model="props['row']['{{$table_field['Field']}}']"  :check-enable="false" :multiple="false" :id="'parent'" :chkbox-type='{ "Y" : "", "N" : "" }' :data="maps['optional_parents']"  :disabled="!props.config['dataUrl']"></ztree>
                                                    @elseif($table_field['showType']=='email')
                                                        <input v-model="props['row']['{{$table_field['Field']}}']" placeholder="请输入{{$table_field['info']}}" type="email" class="form-control"  :disabled="!props.config['dataUrl']">
                                                    @elseif($table_field['showType']=='ueditor')
                                                        <ueditor v-model="props['row']['{{$table_field['Field']}}']" id="{{$table_field['Field']}}" :disabled="!props.config['dataUrl']"  :server-url="global['config']['upload_route']"></ueditor>
                                                    @elseif($table_field['showType']=='select2')
                                                        <select2 v-model="props['row']['{{$table_field['Field']}}']" :default-options="maps['{{$table_field['Field']}}']"  :url="'/admin/{{str_replace('_','-',str_replace('_id','',$table_field['Field']))}}/list'" :keyword-key="'name'" :show="['name']"  :disabled="!props.config['dataUrl']" :is-ajax="true" >
                                                        </select2>
                                                    @elseif($table_field['showType']=='color')
                                                        <colorpicker v-model="props['row']['{{$table_field['Field']}}']" :disabled="!props.config['dataUrl']">
                                                        </colorpicker>
                                                    @elseif($table_field['showType']=='timeSelect')
                                                        <div>
                                                            <el-time-select v-model="props['row']['{{$table_field['Field']}}']" :picker-options="{start: '00:00',step: '00:30',end: '23:30'}" :disabled="!props.config['dataUrl']" placeholder="选择时间">
                                                            </el-time-select>
                                                        </div>
                                                    @elseif($table_field['showType']=='timePicker')
                                                        <div>
                                                            <el-time-picker v-model="props['row']['{{$table_field['Field']}}']" :picker-options="{selectableRange: '00:00:00 - 23:59:59'}" :disabled="!props.config['dataUrl']" placeholder="选择时间点">
                                                            </el-time-picker>
                                                        </div>
                                                    @elseif($table_field['showType']=='switch')
                                                        <div>
                                                            <el-switch v-model="props['row']['{{$table_field['Field']}}']"  :disabled="!props.config['dataUrl']" on-color="#13ce66" off-color="#ff4949" on-value="1" off-value="0">
                                                            </el-switch>
                                                        </div>
                                                    @elseif($table_field['showType']=='slider')
                                                        <div>
                                                            <el-slider v-model="props['row']['{{$table_field['Field']}}']"  :disabled="!props.config['dataUrl']" >
                                                            </el-slider>
                                                        </div>
                                                    @elseif($table_field['showType']=='rate')
                                                        <div>
                                                            <el-rate v-model="props['row']['{{$table_field['Field']}}']" :disabled="!props.config['dataUrl']"  text-template="{value}" show-text text-color="#ff9900">
                                                            </el-rate>
                                                        </div>
                                                    @elseif($table_field['showType']=='num')
                                                        <div>
                                                            <el-input-number  v-model="props['row']['{{$table_field['Field']}}']"  :disabled="!props.config['dataUrl']" :step="1"></el-input-number>
                                                        </div>
                                                    @elseif($table_field['showType']=='upload')
                                                        <upload  v-model="props['row']['{{$table_field['Field']}}']"  :disabled="!props.config['dataUrl']" :action="global['config']['upload_route']+'?action=upload-image'"></upload>
                                                    @elseif($table_field['showType']=='password')
                                                        <input v-model="props['row']['{{$table_field['Field']}}']" placeholder="请输入{{$table_field['info']}}"  type="password" class="form-control"  :disabled="!props.config['dataUrl']">
                                                    @else
                                                        <input v-model="props['row']['{{$table_field['Field']}}']" placeholder="请输入{{$table_field['info']}}" class="form-control" type="text"  :disabled="!props.config['dataUrl']">
                                                    @endif

                                                </div>
                                            @endforeach
                                        </div>
                                @endforeach
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
@if(collect($table_fields)->where('showType','ztree')->isNotEmpty())
            "ztree":function(resolve){require(['public/Ztree.vue'], resolve);}, //树状结构异步组件
@endif
@if(collect($table_fields)->where('showType','ueditor')->isNotEmpty())
            "ueditor":function(resolve){require(['public/Ueditor.vue'], resolve);}, //百度编辑器异步组件
@endif
@if(collect($table_fields)->where('showType','select2')->isNotEmpty())
            "select2":function(resolve){require(['public/Select2.vue'], resolve);} , //选择框异步组件
@endif
@if(collect($table_fields)->where('showType','color')->isNotEmpty())
            "colorpicker":function(resolve){require(['public/Colorpicker.vue'], resolve);}, //颜色选择器异步组件
@endif
@if(collect($table_fields)->where('showType','timeSelect')->isNotEmpty())
            "el-time-select":function(resolve){ require(['element-ui/lib/time-select'], resolve);}, //时间选择器异步组件
@endif
@if(collect($table_fields)->where('showType','timePicker')->isNotEmpty())
            "el-time-picker":function(resolve){require(['element-ui/lib/time-picker'], resolve);}, //时间点选择器异步组件
@endif
@if(collect($table_fields)->where('showType','switch')->isNotEmpty())
            "el-switch":function(resolve){require(['element-ui/lib/switch'], resolve);}, //开关异步组件
@endif
@if(collect($table_fields)->where('showType','slider')->isNotEmpty())
            "el-slider":function(resolve){require(['element-ui/lib/slider'], resolve);}, //滑块异步组件
@endif
@if(collect($table_fields)->where('showType','slider')->isNotEmpty())
            "el-rate":function(resolve){require(['element-ui/lib/rate'], resolve);}, //滑块异步组件
@endif
@if(collect($table_fields)->where('showType','num')->isNotEmpty())
            "el-input-number":function(resolve){require(['element-ui/lib/input-number'], resolve);}, //滑块异步组件
@endif
@if(collect($table_fields)->where('showType','upload')->isNotEmpty())
            "upload":function(resolve){require(['public/Upload.vue'], resolve);}, //滑块异步组件
@endif
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