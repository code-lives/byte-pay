<?php

namespace Byte\Pay;

class BDStorage extends TTPay
{
    protected $setUrl = 'https://developer.toutiao.com/api/apps/set_user_storage';

    protected $removeUrl = 'https://developer.toutiao.com/api/apps/remove_user_storage';

    protected $token;

    public function token(string $accessToken)
    {
        $this->token = $accessToken;

        return $this;
    }

    public function set(array $params, string $session)
    {
        $this->authSetParams($params);
        $body = ['kv_list' => $params['kv_list']];

        $query = [
            'access_token' => $this->token,
            'openid' => $params['openid'],
            'sig_method' => 'hmac_sha256',
            'signature' => hash_hmac('sha256', json_encode($body), $session)
        ];

        $url = $this->setUrl . '?' . http_build_query($query);

        $result = $this->post($url, json_encode($body), ['content-type:application/json']);

        return $this->verifyResultV2($result);
    }

    protected function authSetParams(array $params)
    {
        if (!array_key_exists('openid', $params)) throw new \Exception('缺少参数：openid');

        if (!array_key_exists('kv_list', $params)) throw new \Exception('缺少参数：kv_list');

        if (!is_array($params['kv_list'])) throw new \Exception('参数 kv_list 格式错误,必须是Array');
    }

    public function remove(array $params, string $session)
    {
        $this->authRemoveParams($params);
        $body = ['key' => $params['key']];

        $query = [
            'access_token' => $this->token,
            'openid' => $params['openid'],
            'sig_method' => 'hmac_sha256',
            'signature' => hash_hmac('sha256', json_encode($body), $session)
        ];

        $url = $this->removeUrl . '?' . http_build_query($query);

        $result = $this->post($url, json_encode($body), ['content-type:application/json']);

        return $this->verifyResultV2($result);
    }

    protected function authRemoveParams(array $params)
    {
        if (!array_key_exists('openid', $params)) throw new \Exception('缺少参数：openid');

        if (!array_key_exists('key', $params)) throw new \Exception('缺少参数：key');
    }
}
