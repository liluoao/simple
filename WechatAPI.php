<?php

/**
 * 企业微信API
 *
 * @Author: liluoao
 * @Created: 2017/8/28 15:39
 */

namespace Rmlx\Business\Api;

use GuzzleHttp\Client;
use Rmlx\Business\Qywx\AccessToken;

class WechatAPI {

    /**
     * http client
     *
     * @var Client
     */
    private $http;

    /**
     * 默认参数
     *
     * @var array
     */
    protected static $defaults = [];

    const GET = 'GET';
    const POST = 'POST';

    /**
     * 获取Client
     *
     * @return Client
     */
    public function getHttp() {
        if (is_null($this->http)) {
            $this->http = new Client(['base_uri' => config('app.work_weixin_uri')]);
        }

        return $this->http;
    }

    /**
     * 构建并发起请求
     *
     * @param string $method
     * @param $url
     * @param array $options
     * @return mixed
     */
    public function requestBuild($method = 'GET', $url, $options = []) {
        $method = strtoupper($method);
        $response = $this->getHttp()->request($method, $url, $options);

        return $response->getBody();
    }

    /**
     * get请求
     *
     * @param $url
     * @param array $options
     * @return mixed
     */
    public function get($url, array $options = []) {
        $options = array_merge([
            'access_token' => $this->getAccessToken(),
        ], $options);

        return $this->requestBuild('GET', $url, ['query' => $options, 'verify' => '../resources/assets/cacert.pem']);
    }

    /**
     * post请求
     *
     * @param $url
     * @param array $options
     * @return mixed
     */
    public function post($url, array $options = []) {
        $key = is_array($options) ? 'form_params' : 'body';
        $options = array_merge([
            'access_token' => $this->getAccessToken(),
        ], $options);

        return $this->requestBuild('POST', $url, [$key => $options, 'verify' => '../resources/assets/cacert.pem']);
    }

    /**
     * 发起api网关的请求
     *
     * @param $method
     * @param $url
     * @param array $args
     * @return mixed
     */
    public function callApi($method, $url, array $args) {
        $contents = call_user_func_array([$this, $method], [$url, $args]);

        return $contents;
    }

    /**
     * 获取AccessToken
     *
     * @return string
     */
    public function getAccessToken() {
        $accessToken = new AccessToken(config('app.work_weixin_corp_id'), 'rdhi9Ef6F1aB2OK4WK8Ck7NsbA7C25g58chRTHpSVBE');

        return $accessToken->getToken();
    }
}
