<?php

/**
 * 企业微信控制器
 *
 * @Author: liluoao
 * @Created: 2017/9/4 11:10
 */

namespace App\Http\Controllers\Qywx;

use App\Http\Requests\Order\QywxOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Rmlx\Business\Api\ApiServer;
use Rmlx\Business\ErrorMessage;
use Rmlx\Business\Order\Order as BasicOrder;
use Rmlx\Business\Queue\Queue as BasicQueue;
use Rmlx\Business\Qywx\Order;
use Rmlx\Business\Qywx\Queue;
use Rmlx\Business\Qywx\Qywx;
use Rmlx\Business\Qywx\User;
use Rmlx\Models\System\AdminModel;
use Rmlx\Models\System\MerchantModel;

class QywxController {

    const NO_RESERVE_STATE = 0;//未预约状态
    const NO_FINISH_STATE = 0;//未完成状态
    const NO_PAID_STATE = 0;//未支付状态

    /**
     * 移动端登陆
     */
    public function appWorkWechatLogin() {
        $merchantId = session('merchant_id', 0) ?: 0;
        $qywx = new Qywx();
        $appId = $qywx->getCorpId($merchantId);
        $agentId = $qywx->getAgentId($merchantId);
        $redirect_uri = urlencode('http://xxx/qywx/redirect');
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appId&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_base&agentid=$agentId#wechat_redirect";
        header("Location:{$url}");
    }

    /**
     * 获取登陆用户信息
     *
     * @return array|string
     */
    public function getWorkWechatUserByCode() {
        $code = Input::get('code');
        $user = new User($code);
        $wechatUserId = $user->getUserId();
        $admin = AdminModel::query()->where('enter_wechat', '=', $wechatUserId)->first();
        if (empty($admin)) {
            return ErrorMessage::nonExistent();
        }
        if ($admin->status != 1) {
            return ErrorMessage::nonExistent();
        }

        $merchant = MerchantModel::query()->find($admin->merchans_id);
        Session::put([
            'merchant_id' => $admin->merchans_id,//商户ID
            'merchant_name' => $merchant->name,//商户名
            'user_id' => $admin->admin_id,//用户ID
            'store_id' => $admin->sid,//门店ID
            'username' => $admin->admin_name,//用户名
            'mobile' => $admin->mobile,//手机号
            'department' => $admin->dept,//部门名
            'position' => $admin->postion,//职务
            'email' => $admin->email,//邮箱
            'wechat_userId' => $wechatUserId,//企业微信UserId
            'system_page_size' => 20//每页显示数量
        ]);

        return session('username') . '登陆成功';
    }

    /**
     * 打开企业微信扫描页(网页登陆)
     */
    public function webWorkWechatLogin() {
        $merchantId = session('merchant_id', 0) ?: 0;
        $qywx = new Qywx();
        $appId = $qywx->getCorpId($merchantId);
        $agentId = $qywx->getAgentId($merchantId);
        $urlParam = http_build_query([
            'appid' => $appId,
            'agentid' => $agentId,
            'redirect_uri' => 'http://xxx/qywx/bind'
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
        $user = new User($code);
        $userId = $user->getUserId();
        $admin = AdminModel::query()->where('enter_wechat', '=', $userId)->get();
        if (count($admin)) {
            return '已绑定其他账户';
        }
        $loginAdmin = AdminModel::query()->find(session('user_id'))->update(['enter_wechat' => $userId]);
        if (!$loginAdmin) {
            return '绑定失败';
        }
        Session::put('wechat_userId', $userId);

        return '成功';
    }

    /**
     * 获取企业微信用户详情
     *
     * @return mixed
     */
    public function getWechatUser() {
        $userId = session('wechat_userId');
        $merchantId = session('merchant_id');
        $admin = new Qywx();
        $data = $admin->getWechatUser($merchantId, $userId);

        return $data;
    }
}
