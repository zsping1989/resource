<template>
    <div>
        <el-row>
            <el-col :span="8">
                <edit :ftx-data="editData" :ftx-config="editConfig">
                    <template scope="props">
                        @foreach ($table_fields as $table_field)
                            @if($table_field['showType']=='hidden' || in_array($table_field['Field'],['id','created_at','updated_at']))
                                @continue
                            @endif
                            <el-form-item label="{{$table_field['info']}}：" :class="{'is-error':props.error['{{$table_field['Field']}}']}">
                                @if($table_field['showType']=='time')
                                    <el-date-picker
                                            :editable="false"
                                            v-model="props.row['{{$table_field['Field']}}']"
                                            type="date"
                                            :clearable="false"
                                    @change="props.row['{{$table_field['Field']}}'] = arguments[0]"
                                    placeholder="选择日期">
                                    </el-date-picker>
                                @elseif($table_field['showType']=='textarea')
                                    <el-input type="textarea" :rows="5" v-model="props.row['{{$table_field['Field']}}']">
                                    </el-input>
                                @elseif($table_field['showType']=='radio')
                                    <el-select v-model="props.row['{{$table_field['Field']}}']" placeholder="请选择">
                                        <el-option
                                                v-for='(item,key) in {!! json_encode($table_field['values']) !!}'
                                                :label="item"
                                                :value="key">
                                        </el-option>
                                    </el-select>
                                @else
                                    <el-input name="{{$table_field['Field']}}" type="text" v-model="props.row['{{$table_field['Field']}}']"></el-input>
                                @endif
                                <div v-show="props.error['{{$table_field['Field']}}']" v-for="error in props.error['{{$table_field['Field']}}']"
                                     class="el-form-item__error">@{{error}}
                                </div>
                            </el-form-item>

                        @endforeach
                    </template>
                </edit>
            </el-col>
        </el-row>
    </div>
</template>

<script>
    import edit from '../../public/Edit.vue';
    export default {
        components: {
            "edit": edit
        },
        data(){
            return {
                editData: datas.row,
                editConfig: {
                    dataUrl: '/admin/{{$path}}/edit',
                    backUrl: '/admin/{{$path}}/index',
                    rules: {
                        name: [
                            {required: true, message: '请输入名称', trigger: 'blur'}
                        ]
                    }
                }
            }
        }
    }
</script>
