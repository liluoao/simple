<?php
/**
 * 抽象API层
 *
 * @Author: lla
 * @Created: 2017/11/1 9:38
 */

namespace Api;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use ApiException;

class AbstractAPI {

    /**
     * Token错误码
     */
    const TOKEN_UNEXIST = 40014;
    const TOKEN_EXPIRE = 42001;

    /**
     * http client
     *
     * @var Client
     */
    private $http;

    /**
     * http client base uri
     *
     * @var string
     */
    private $base_uri;

    /**
     * token类
     *
     * @var null
     */
    private $access_token = null;

    /**
     * 是否添加token
     *
     * @var bool
     */
    private $add_token = false;

    /**
     * 获取Token的参数
     *
     * @var array
     */
    private $token_params = [];

    /**
     * 默认参数
     *
     * @var array
     */
    protected static $defaults = [];

    const GET = 'GET';
    const POST = 'POST';

    /**
     * API构造函数
     *
     * tokenConfig 中
     * base_uri 是必须,获取Token的Base Uri
     * url 是必须,获取Token的URL
     * cache_key 是必须,Token的缓存键
     *
     * @created 2017-11-1
     * @updated 2017-11-7
     * @param $baseUri
     * @param bool $addToken 是否给路由添加Token,默认为不添加
     * @param array $tokenConfig 需要添加Token时,用于获取Token的参数
     */
    public function __construct($baseUri, $addToken = false, $tokenConfig = []) {
        $this->setBaseUri($baseUri);
        if ($addToken) {
            $this->setAddToken(true);
            $this->setTokenParams($tokenConfig);
            $this->access_token = new AccessToken($this->getTokenParams());
        }
    }

    /**
     * 获取Client
     *
     * @return Client
     */
    public function getHttp() {
        if (is_null($this->http)) {
            $this->http = new Client(['base_uri' => $this->getBaseUri()]);
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
        if ($this->isAddToken()) {
            $options['handler'] = $this->getHandler();
        }
        $response = $this->getHttp()->request($method, $url, $options);

        return $response->getBody();
    }

    /**
     * 处理器
     * 使用AccessToken中间件
     *
     * @return HandlerStack
     */
    public function getHandler() {
        $handler = new CurlHandler();
        $stack = HandlerStack::create($handler);
        $stack->push($this->accessTokenMiddleware());

        return $stack;
    }

    /**
     * AccessToken中间件
     * 为请求添加access_token
     *
     * @return \Closure
     */
    protected function accessTokenMiddleware() {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $token = $this->access_token->getToken();

                $request = $request->withUri(Uri::withQueryValue($request->getUri(), 'access_token', $token));

                return $handler($request, $options);
            };
        };
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

        return $this->requestBuild('GET', $url, ['query' => $options]);
    }

    /**
     * post请求
     *
     * @param $url
     * @param array $options
     * @return mixed
     */
    public function post($url, $options = []) {
        $key = is_array($options) ? 'form_params' : 'body';
        $options = array_merge(self::$defaults, $options);

        return $this->requestBuild('POST', $url, [$key => $options]);
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
        $contents = call_user_func_array([$this, $method], [$url, $args]);
        if (empty($contents)) {
            throw new ApiException(400, 'Request fail. response: ' . json_encode($contents, JSON_UNESCAPED_UNICODE));
        }
        $result = $this->checkAndThrow($contents);

        if ($result === 'tokenFail') {
            //重新获取token,发起请求
            $this->access_token->getToken(true);
            $contents = call_user_func_array(array($this, $method), array($url, $args));
            $this->checkAndThrow($contents);
        }

        return $contents;
    }

    /**
     * post发送json数据
     *
     * @param $url
     * @param array $args
     * @return \Psr\Http\Message\StreamInterface
     * @throws ApiException
     */
    public function json($url, array $args) {
        //如果有默认参数
        $args = array_merge(self::$defaults, $args);
        $options = [
            'body' => json_encode($args, JSON_UNESCAPED_UNICODE),
            'headers' => [
                'Accept' => 'application/json'
            ]
        ];

        return $this->requestBuild('post', $url, $options);
    }

    /**
     * base uri getter
     *
     * @return string
     */
    public function getBaseUri() {
        return $this->base_uri;
    }

    /**
     * base uri setter
     *
     * @param string $base_uri
     * @return void
     */
    public function setBaseUri($base_uri) {
        $this->base_uri = $base_uri;
    }

    /**
     * @return array
     */
    public function getTokenParams() {
        return $this->token_params;
    }

    /**
     * @param array $token_params
     */
    public function setTokenParams($token_params) {
        $this->token_params = $token_params;
    }

    /**
     * @return bool
     */
    public function isAddToken() {
        return $this->add_token;
    }

    /**
     * @param bool $add_token
     */
    public function setAddToken($add_token) {
        $this->add_token = $add_token;
    }

    /**
     * 检查响应数据格式
     *
     * @param $responseObj
     * @return string
     * @throws ApiException
     */
    public function checkAndThrow($responseObj) {
        if (isset($responseObj->errcode) && 0 !== $responseObj->errcode) {
            if ($responseObj->errcode === self::TOKEN_UNEXIST || $responseObj->errcode === self::TOKEN_EXPIRE) {
                //Token不存在或过期，重新获取Token并发起请求
                return 'tokenFail';
            }

            throw new ApiException($responseObj->errcode, $responseObj->errmsg);
        }
    }
}
