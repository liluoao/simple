<?php

/**
 * AccessToken
 *
 * @Author: liluoao
 * @Created: 2017/8/18 17:18
 */

namespace Rmlx\Business\Qywx;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Rmlx\Exceptions\ApiException;

class AccessToken {

    /**
     * App ID.
     *
     * @var string
     */
    protected $corpId;

    /**
     * App secret.
     *
     * @var string
     */
    protected $corpSecret;

    /**
     * Cache.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Cache Key.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * http client
     *
     * @var Client
     */
    protected $http;

    /**
     * Response Json key name.
     *
     * @var string
     */
    protected $tokenJsonKey = 'access_token';

    //API
    const API_TOKEN_GET = 'gettoken';

    /**
     * Constructor.
     *
     * @param $corpId
     * @param $corpSecret
     * @param Cache $cache
     */
    public function __construct($corpId, $corpSecret, Cache $cache = null) {
        $this->corpId = $corpId;
        $this->corpSecret = $corpSecret;
        $this->cache = $cache;
    }

    /**
     * Get token from WeChat API.
     *
     * @param bool $forceRefresh
     *
     * @return string
     */
    public function getToken($forceRefresh = false) {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();
        $cached = $cache::get($cacheKey);
        if ($forceRefresh || empty($cached)) {
            $token = $this->getTokenFromServer();
            // XXX: T_T... 7200 - 1500
            $cache::put($cacheKey, $token[$this->tokenJsonKey], $token['expires_in'] - 1500);

            return $token[$this->tokenJsonKey];
        }

        return $cached;
    }

    /**
     * Return the app id.
     *
     * @return string
     */
    public function getCorpId() {
        return $this->corpId;
    }

    /**
     * Return the secret.
     *
     * @return string
     */
    public function getCorpSecret() {
        return $this->corpSecret;
    }

    /**
     * Set cache instance.
     *
     * @param Cache $cache
     *
     * @return AccessToken
     */
    public function setCache(Cache $cache) {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Return the cache manager.
     *
     * @return Cache
     */
    public function getCache() {
        return $this->cache ?: $this->cache = new Cache();
    }

    /**
     * Get the access token from WeChat server.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws ApiException
     */
    public function getTokenFromServer() {
        $params = [
            'corpid' => $this->corpId,
            'corpsecret' => $this->corpSecret
        ];
        $token = $this->getHttp()->request('GET', self::API_TOKEN_GET, [
            'query' => $params,
            'verify' => '../resources/assets/cacert.pem'
        ])->getBody();
        if (empty($token)) {
            throw new ApiException(400, 'Request AccessToken fail. response: ' . json_encode($token, JSON_UNESCAPED_UNICODE));
        }

        return json_decode($token,true);
    }

    /**
     * 获取Client
     *
     * @return Client
     */
    public function getHttp() {
        return $this->http ?: $this->http = new Client(['base_uri' => config('app.work_weixin_uri')]);
    }

    /**
     * Set the http instance.
     *
     * @param Client $http
     *
     * @return $this
     */
    public function setHttp(Client $http) {
        $this->http = $http;

        return $this;
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
            return $this->corpId;
        }

        return $this->cacheKey;
    }
}
