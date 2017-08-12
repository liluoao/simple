<template>
    <div>
        <div class="g-head">
            <div class="g-nav">
                <el-row>
                    <el-col :span="14">
                        <div v-for="(btn,index) in btns" @click="show(index)">
                            <el-col :span="3">
                                <router-link tag='div' :to='btn.link' class="grid-content" :id="index" exact>{{btn.text}}</router-link>
                            </el-col>
                        </div>
                    </el-col>
                    <el-col :span="8" :offset="2">
                        <div class="f-fl">
                            <div class="u-face">
                                <img src="../../common/img/c.jpg"/>
                            </div>
                            <div class="u-welcome">
                                <div>欢迎您:{{adminData.admin_name}}</div>
                                <div>{{adminData.merchant_name}}</div>
                            </div>
                        </div>
                        <div class="f-fl">
                            <router-link tag="span" to="/customer/cuslist">
                                <span class="icon-bell"></span>
                                <div style="cursor: pointer">提醒{{messageNum}}</div>
                            </router-link>
                        </div>
                        <div class="f-fl f-quit">
                            <span class="icon-power-off"></span>
                            <div @click="logout()">退出</div>
                        </div>
                    </el-col>
                </el-row>
            </div>
        </div>
        <div class="g-mn">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'home',
        data() {
            return {
                page: 0,
                btns: [
                    {link: '/index', text: '首页'},
                    {link: '/customer', text: '客户管理'},
                    {link: '/servers', text: '服务开单'},
                    {link: '/trade', text: '交易管理'},
                    {link: '/financial', text: '财务管理'},
                    {link: '/inventory', text: '库存管理'},
                    {link: '/sysset', text: '系统设置'}
                ],
                checked: 0,
                admin_name:123,
                adminData:{},
                messageNum:0,
            }
        },
        mounted() {
            this.getLogin('/auth/getlogin', {});
            this.getMessageNum('/select/getmessagenum', {});
        },
        methods: {
            show: function (i) {
                this.checked = i;
            },
            // 获取
            getLogin(url, param) {
                var fullUrl = _g.buildUrl(url);
                _api.get(fullUrl, param).then(value => {
                    this.adminData = value.data;
                    // console.log(this.adminData);
                });
            },

            // 退出
            logout(url, param){
                _api.get(_g.buildUrl('/auth/logout'), param).then(data => {
                    if (data.errcode == '0') {
                        _g.toastMsg('success', data.errmsg,1000);
                        this.$router.push('/login');
                    } else {
                        _g.toastMsg('error', data.errmsg,1000);
                    }
                });
            },

            // 获取提醒消息条数
            getMessageNum(url, param) {
                var fullUrl = _g.buildUrl(url);
                _api.get(fullUrl, param).then(value => {
                    console.log(value);
                    this.messageNum = value.data;
                });
            },
        }
    }
</script>

<style scoped>
    .g-mnc1 {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        overflow: auto;
        background: #fff;
        padding: 15px;
    }
    .grid-content:hover {
        background: #22ABBD;
        cursor: pointer;
    }
    .grid-content {
        line-height: 50px;
    }
    .g-nav .router-link-active {
        background: #22ABBD;
    }
    .u-face {
        width: 38px;
        height: 38px;
        border-radius: 100%;
        float: left;
        overflow: hidden;
        margin-right: 5px;
    }
    .u-welcome {
        float: left;
        text-align: left;
    }
    .u-face img {
        max-width: 100%;
    }
    .f-fl {
        margin-left: 15px;
        margin-top: 8px;
    }
    .f-quit {
        cursor: pointer;
    }
</style>
