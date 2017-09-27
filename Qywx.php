<?php

use Illuminate\Support\Facades\Cache;
use QyWechatAPI;
use MerchantQywx;
use MerchantQywxApp;

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
}
