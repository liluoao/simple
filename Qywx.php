<?php

/**
 * 企业微信逻辑类
 *
 * @Author: liluoao
 * @Created: 2017/9/4 14:41
 */

namespace Rmlx\Business\Qywx;

use Illuminate\Support\Facades\Cache;
use Rmlx\Business\Api\QyWechatAPI;
use Rmlx\Models\System\MerchantQywx;
use Rmlx\Models\System\MerchantQywxApp;

class Qywx {

    const API_USER_GET = 'user/get';

    /**
     * @var string
     */
    private $_wxServiceUrl = 'https://qyapi.weixin.qq.com';

    /**
     * 企业微信ID
     * @var string
     */
    private $_corpId = '-';

    /**
     * 企业微信secret
     * @var string
     */
    private $_corpSecret = '-';

    /**
     * 应用ID
     * @var string
     */
    private $_agentId = '-';

    /**
     * 应用secret
     * @var string
     */
    private $_appSecret = '-';

    /**
     * 获取AppId(即corpId)
     *
     * @param $merchantId
     * @return mixed|string
     */
    public function getCorpId($merchantId = 0) {
        $CorpId = Cache::get('corpId');//先从Cache中获取

        if (!empty($CorpId)) {
            return $CorpId;
        }

        $qywx = MerchantQywx::query()
            ->select(['corp_id'])
            ->where('merchant_id', '=', $merchantId)
            ->first();

        if ($qywx) {
            Cache::put('corpId', $qywx->corp_id, 6000);//将新的appId存入Cache
            $CorpId = $qywx->corp_id;
        } else {
            $CorpId = $this->_corpId;
        }

        return $CorpId;
    }

    /**
     * 获取agentId(应用ID)
     *
     * @param $merchantId
     * @return mixed|string
     */
    public function getAgentId($merchantId = 0) {
        $agentId = Cache::get('agentid');//先从Cache中获取

        if (!empty($agentId)) {
            return $agentId;
        }

        $qywx_app = MerchantQywxApp::query()->select(['app_id'])->where([
            ['merchant_id', '=', $merchantId],
            ['app_name', '=', '-']
        ])->first();
        if ($qywx_app) {
            Cache::put('agentid', $qywx_app->app_id, 6000);//将新的agentId存入Cache
            $agentId = $qywx_app->app_id;
        } else {
            $agentId = $this->_agentId;
        }

        return $agentId;
    }

    /**
     * 获取企业微信的CorpSecret(微信secret)
     *
     * @param $merchantId
     * @return mixed|string
     */
    public function getCorpSecret($merchantId = 0) {
        $corpSecret = Cache::get('corpSecret');//先从Cache中获取

        if (!empty($corpSecret)) {
            return $corpSecret;
        }

        $qywx = MerchantQywx::query()
            ->select(['corp_secret'])
            ->where('merchant_id', '=', $merchantId)
            ->first();
        if ($qywx) {
            Cache::put('corpSecret', $qywx->corp_secret, 6000);//将新的corp_secret存入Cache
            $corpSecret = $qywx->corp_secret;
        } else {
            $corpSecret = $this->_corpSecret;
        }

        return $corpSecret;
    }

    /**
     * 获取appSecret(应用secret)
     *
     * @param int $merchantId
     * @return mixed|string
     */
    public function getAppSecret($merchantId = 0) {
        $appSecret = Cache::get('appSecret');//先从Cache中获取
        if (!empty($appSecret)) {
            return $appSecret;
        }

        $qywx_app = MerchantQywxApp::query()->select(['app_secret'])->where([
            ['merchant_id', '=', $merchantId],
            ['app_name', '=', '-']
        ])->first();
        if ($qywx_app) {
            Cache::put('appSecret', $qywx_app->app_secret, 6000);//将新的AppSecret存入Cache
            $appSecret = $qywx_app->app_secret;
        } else {
            $appSecret = $this->_appSecret;
        }

        return $appSecret;
    }

    /**
     * 获取企业微信用户详情
     *
     * @param $merchantId
     * @param $userId
     * @return mixed
     */
    public function getWechatUser($merchantId = 0, $userId) {
        $api = new QyWechatAPI();
        $corpId = $this->getCorpId($merchantId);
        $corpSecret = $this->getCorpSecret($merchantId);
        $accessToken = $api->getAccessToken($corpId, $corpSecret);
        $data = $api->callApi('GET', self::API_USER_GET, ['userid' => $userId, 'access_token' => $accessToken]);

        return $data;
    }

    /**
     * @param $source
     * @return array
     */
    public function getSignPackage($source) {
        // 获取ticket值
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        if ($source) {
            $url = $_SERVER['HTTP_REFERER'];
        } else {
            $url = "{$protocol}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        }


        $timestamp = time();
        $nonceStr = $this->createNonceStr(); // 创建随机字符串
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->_corpId,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );

        return $signPackage;
    }


    /**
     * 获取ticket值
     *
     * @param bool $forceRefresh 是否强制刷新
     * @return false|mixed
     */
    public function getJsApiTicket($forceRefresh = false) {
        $token = $this->requestJsApiTicket();

        return $token->ticket;
    }

    /**
     * 请求ticket
     *
     * @return string 返回ticket值
     */
    public function requestJsApiTicket() {
        // 获取access_token值
        $accessToken = $this->getAccessToken();
        $api = new QyWechatAPI();
        $data = array(
            'access_token' => $accessToken
        );
        // 发送请求获取ticket
        $returnResultArr = json_decode($api->callApi('get', $this->_wxServiceUrl . '/cgi-bin/get_jsapi_ticket', $data));
        if ($returnResultArr->errcode != '0') {

            return false;
        }

        return $returnResultArr;
    }

    /**
     * 获取access_token值
     * 到期会自动重新获取、续期
     *
     * @param bool $forceRefresh 是否强制刷新
     * @return false|mixed
     */
    public function getAccessToken($forceRefresh = false) {
        $token = $this->requestAccessToken();


        return $token->access_token;
    }

    /**
     * 请求access_token
     *
     * @return string 返回access_token值
     */
    public function requestAccessToken() {
        $api = new QyWechatAPI();
        $data = array(
            'corpid' => $this->_corpId,
            'corpsecret' => $this->_corpSecret
        );

        $tokenArr = json_decode($api->callApi('get', $this->_wxServiceUrl . '/cgi-bin/gettoken', $data));
        if (isset($tokenArr->access_token)) {
            return $tokenArr;
        }

        return false;
    }

    /**
     * 创建随机字符串
     *
     * @param int $length
     * @return string
     */
    public function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }
}
