<?php

class AdminController {

    /**
     * 打开企业微信扫描页
     */
    public function workWechatLogin() {
        if (!config('app.work_weixin_config')) {
            return '不允许对接微信';
        }
        $urlParam = http_build_query([
            'appid' => config('app.work_weixin_corp_id'),
            'agentid' => '1000002',
            'redirect_uri' => 'http://www.laravel.cn/admin/redirect'
        ]);
        $url = 'https://open.work.weixin.qq.com/wwopen/sso/qrConnect?' . $urlParam;
        header("Location:{$url}");
    }

    /**
     * 将扫描的微信userId与登陆的用户绑定
     *
     * @return string
     */
    public function bindWorkWechatUser() {
        $code = Input::get('code');
        $userId = $this->getWechatUserId($code);
        $admin = AdminModel::query()->where('enter_wechat', '=', $userId)->get();
        if (count($admin)) {
            return '已绑定其他账户';
        }
        $loginAdmin = AdminModel::query()->find(session('user_id'))->update(['enter_wechat' => $userId]);
        if (!$loginAdmin) {
            return '绑定失败';
        }
        Session::put('wechat_userId',$userId);
        return '成功';
    }

    /**
     * 获取企业微信用户详情
     *
     * @return mixed
     */
    public function getWechatUser() {
        $userId = session('wechat_userId');
        $admin = new Admin();
        $data = $admin->getWechatUser($userId);
        return $data;
    }

    /**
     * 获取到企业微信userId
     *
     * @param $code
     * @return mixed
     */
    public function getWechatUserId($code) {
        $userId = Cache::get(config('app.work_weixin_user_id_cache_key'));
        if (empty($userId)) {

            $user = new User($code);
            $userId = $user->getUserId();
        }

        return $userId;
    }

    /**
     * 获取企业微信部门列表
     *
     * @return mixed
     */
    public function getWechatDepartmentList() {
        $admin = new Admin();
        $data = $admin->getWechatDepartmentList();
        return $data;
    }

    /**
     * 获取企业微信部门成员
     *
     * @return mixed
     */
    public function getWechatUserList() {
        $departmentId = Input::get('department_id');
        $admin = new Admin();
        $data = $admin->getWechatUserList($departmentId);
        return $data;
    }
}
