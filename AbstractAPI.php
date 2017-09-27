<?php


use GuzzleHttp\Client;
use Base;
use ApiException;

class AbstractAPI extends Base {

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
            $this->http = new Client(['base_uri' => config('app.uc_host')]);
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
        $this->log->debug('请求参数', $args);
        $contents = call_user_func_array([$this, $method], [$url, $args]);
        if (empty($contents)) {
            $this->log->error('返回结果', json_decode($contents, true));
            throw new ApiException(400, 'Request fail. response: ' . json_encode($contents, JSON_UNESCAPED_UNICODE));
        }
        $this->log->debug('返回结果', json_decode($contents, true));

        return $contents;
    }

    /**
     * post发送json数据
     *
     * @param $url
     * @param $args
     * @return \Psr\Http\Message\StreamInterface
     */
    public function postJson($url, $args) {
        $contents = $this->getHttp()->post($url, ['json' => $args]);

        return $contents->getBody();
    }
}
