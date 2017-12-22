<?php
/**
 * 获取AccessToken
 *
 * @Author: lla
 * @Created: 2017/11/1 10:47
 */

namespace Api;

use Illuminate\Support\Facades\Cache;
use ApiException;

class AccessToken {

    private $token_params = [];

    /**
     * Cache Key.
     *
     * @var string
     */
    protected $cacheKey = '';

    /**
     * Response Json key name.
     *
     * @var string
     */
    protected $tokenJsonKey = 'access_token';


    /**
     * Constructor.
     *
     * @param array $tokenParams
     */
    public function __construct($tokenParams = []) {
        $this->setTokenParams($tokenParams);
        $this->setCacheKey($tokenParams['cache_key']);
    }

    /**
     * Get token from API.
     *
     * @param bool $forceRefresh
     * @return bool
     * @throws ApiException
     */
    public function getToken($forceRefresh = false) {
        $cacheKey = $this->getCacheKey();
        $cached = Cache::get($cacheKey);
        if ($forceRefresh || empty($cached)) {
            $token = $this->getTokenFromServer();
            if (!isset($token[$this->tokenJsonKey])) {
                throw new ApiException(1000, '获取token失败');
            }
            //放入缓存
            Cache::put($cacheKey, $token[$this->tokenJsonKey], $token['expires_in'] - 1500);

            return $token[$this->tokenJsonKey];
        }

        return $cached;
    }

    /**
     * Get the access token from server.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws ApiException
     */
    public function getTokenFromServer() {
        $params = $this->getTokenParams();
        $api = new AbstractAPI($params['base_uri']);
        $url = $params['url'];
        $method = $params['method']??'GET';
        $tokenType = $params['tokenType'] ?? 'wechat';
        unset($params['base_uri']);
        unset($params['url']);
        unset($params['method']);
        $token = $api->callApi(strtoupper($method), $url, $params);
        if ($tokenType === 'wechat') {
            return json_decode($token, true);
        } else {
            $this->setCacheKey($tokenType);

            return json_decode($token, true)['data'];
        }
    }

    /**
     * Set access token cache key.
     *
     * @param string $cacheKey
     *
     * @return $this
     */
    public function setCacheKey($cacheKey) {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * Get access token cache key.
     *
     * @return string $this->cacheKey
     */
    public function getCacheKey() {
        if (is_null($this->cacheKey)) {
            return '默认KEY';
        }

        return $this->cacheKey;
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

}
