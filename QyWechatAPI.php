<?php

namespace Rmlx\Business\Api;

use GuzzleHttp\Client;
use Base;
use AccessToken;
use ApiException;

class QyWechatAPI extends Base {

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
     * Base constructor.
     */
    public function __construct() {
        //设置log名称
        $this->setLogNamePath('api_request', 'api_log');
        parent::__construct();
    }

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
        $options = array_merge(self::$defaults, $options);

        return $this->requestBuild('GET', $url, ['query' => $options, 'verify' => '../resources/assets/cacert.pem']);
    }

    /**
     * post请求 + get参数AccessToken
     *
     * @param $url
     * @param array $options
     * @return mixed
     */
    public function post($url, array $options = []) {
        $key = is_array($options) ? 'form_params' : 'body';
        $options = array_merge(self::$defaults, $options);
        $AccessToken = $options['access_token'];
        unset($options['access_token']);

        return $this->requestBuild('POST', $url . "?access_token=$AccessToken", [
            $key => $options,
            'verify' => '../resources/assets/cacert.pem'
        ]);
    }

    /**
     * 发起api网关的请求
     *
     * @param $method
     * @param $url
     * @param array $args
     * @return mixed
     * @throws ApiException
     */
    public function callApi($method, $url, array $args) {
        $this->log->debug('请求参数',$args);
        $contents = call_user_func_array([$this, $method], [$url, $args]);
        if (empty($contents)) {
            $this->log->error('返回结果',json_decode($contents,true));
            throw new ApiException(400, 'Request fail. response: ' . json_encode($contents, JSON_UNESCAPED_UNICODE));
        }
        $this->log->debug('返回结果',json_decode($contents,true));
        return $contents;
    }

    /**
     * 获取应用的AccessToken
     *
     * @param $corpId
     * @param $corpSecret
     * @return string
     */
    public function getAccessToken($corpId, $corpSecret) {
        $accessToken = new AccessToken($corpId, $corpSecret);

        return $accessToken->getToken(true);
    }
}
