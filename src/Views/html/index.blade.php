<template>
    <section class="content">
        <data-table :ftx-params="options" :ftx-data="list" :ftx-config="config">
            <template scope="props" slot="sizer-where">
                <div class="input-group input-group-sm" style="width:165px;">
                    <input class="form-control" v-model="props.where['id']" placeholder="请输入关键字" type="text">
                </div>
            </template>
            <template scope="props" slot="table-content-header">
                <th v-for="(item,key) in props.fields" :class="item['class'] ? item['class']:''">
                    @{{ item.name }}
                    <span v-if="item.order" class="glyphicon" @click="props.sorder(item.orderField ? item.orderField : key)" :class="{'glyphicon-sort':!props.order[item.orderField ? item.orderField : key],'glyphicon-sort-by-attributes-alt':props.order[item.orderField ? item.orderField : key]=='desc','glyphicon-sort-by-attributes':props.order[item.orderField ? item.orderField : key]=='asc'}"></span>
                </th>
            </template>
            <template scope="props" slot="table-content-row">
                <td v-for="(value,key) in props.fields"  :class="value['class'] ? value['class']:''">
                     <span v-if="0"></span>
                    @foreach($show_fields as $key=>$show_field)
                        @if($table_fields[$key]['showType']=='checkbox')
                            <span v-else-if="key =='{{$key}}'">
                               <span class="label" v-for="value in props.item[key]" :class="value | checkbox_class(2,statusClass)" style="margin-left: 5px;">
                                    @{{ maps[key][value] }}
                                </span>
                            </span>
                        @elseif($table_fields[$key]['showType']=='radio')
                            <span v-else-if="key =='{{$key}}'">
                                <span class="label" :class="'label-'+statusClass[props.item[key]%statusClass.length]">@{{ maps[key][props.item[key]] }}</span>
                            </span>
                        @elseif($table_fields[$key]['showType']=='date')
                            <span v-else-if="key =='{{$key}}'">
                                @{{ props.item | array_get(key) | str_limit(10,'') }}
                            </span>
                        @elseif($table_fields[$key]['showType']=='icon')
                            <span v-else-if="key =='{{$key}}'">
                                 <i class="fa" :class="props.item[key]"></i>
                             </span>
                        @endif
                    @endforeach
                    <span v-else-if="key.indexOf('.')!=-1">
                        @{{ props.item | array_get(key) }}
                    </span>
                    <span v-else>
                        @{{ props.item[key] }}
                    </span>
                </td>
            </template>
        </data-table>
    </section>
</template>

<script>
    export default {
        components: {
        },
        data(){
            var data = this.$store.state;
            data.config = {
                dataUrl: data.configUrl.listUrl, //数据获取地址
                editUrl: data.configUrl.showUrl, //数据编辑页面
                destroyUrl: data.configUrl.destroyUrl, //删除数据地址
                exportUrl: data.configUrl.exportUrl,
                fields: {!! json_encode($show_fields,JSON_UNESCAPED_UNICODE) !!},
                operation: true //需要操作列
            };
            return data;
        },
        mounted() {

        },
        methods: {
            //修改数据源
            updateData: function (datas) {
                this.lists = datas;
            }
        }
    }
</script>