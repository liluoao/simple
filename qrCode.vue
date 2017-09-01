<template>
    <div>
        <div class="u-btns">
            <router-link to="addCode">
                <el-button type="primary"  icon="plus">创建二维码</el-button>
            </router-link>
        </div>
        <div class="bg-form">
            <el-form :inline="true" :model="codeForm" ref="codeForm" class="demo-form-inline">
                <el-form-item label="门店">
                    <el-select v-model="codeForm.store_name" placeholder="门店">
                        <el-option label="不限" value=""></el-option>
                        <el-option
                            v-for="store in storeOptions"
                            :key="store.sid"
                            :label="store.store_name"
                            :value="store.store_name"
                        ></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item>
                    <el-button type="primary" @click="queryCodes('codeForm')">查询</el-button>
                </el-form-item>
            </el-form>
        </div>
        <el-table :data="qrCode" style="width: 100%">
            <el-table-column prop="store_name" label="门店名称">
            </el-table-column>
            <el-table-column prop="store_img" label="二维码">
                <template scope="scope">
                    <img :src="scope.row.code_img" class="t-code">
                </template>
            </el-table-column>
            <el-table-column prop="create_time" label="创建时间">
            </el-table-column>
            <el-table-column label="操作">
                <template scope="scope">
                    <el-button type="text" @click="checkCode(scope.row)" size="small">查看</el-button>
                    <el-button type="text" size="small">下载</el-button>
                </template>
            </el-table-column>
        </el-table>
        <el-dialog :visible.sync="codeVisible" size='tiny'>
            <img :src="codeUrl">
        </el-dialog>
        <pagination :to-page="toPage" @form-page="fromPage"></pagination>
    </div>
</template>

<script>
    import pagination from '../common/pagination.vue';
export default {
    name: 'qrCode',
    components: {
        'pagination':pagination
    },
    data() {
        return {
            codeForm: {
                store_name: ''
            },
            codeVisible:false,
            qrCode: [],
            storeOptions:[],
            codeUrl:'',
            toPage:{
                currentPage:null,
                totalCount:null,
            },
            
            coList:'/qrcode/qrcodelist',
            mStore:'/select/getmerchansstore',
        }
    },
    mounted(){
        this.getCodeList();
        _g.getStores.call(this,this.mStore);//获取门店下拉列表
    },
    methods: {
        getCodeList(params){
            _api.get(this.coList, params).then(data => {
                if (data.errcode == '0') {
                    this.qrCode = data.data.data;
                    this.toPage.totalCount = data.data.total;
                    this.toPage.currentPage = data.data.current_page;
                }
            });
        },
        //查询二维码
        queryCodes(form){
            //将表单表单数据对象赋值给model变量
            var params=this.$refs[form].model;
            this.getCodeList(arams);
        },
        //查看二维码
        checkCode(row){
            this.codeVisible=true;
            this.codeUrl=row.code_img;
        },
        //获取分页请求的page的值
        fromPage(val){
            this.getCodeList({page:val});
        }
    }
}
</script>

<style cope>
    .check-code{
        opacity: .5;
        background: #000;
    }

</style>
