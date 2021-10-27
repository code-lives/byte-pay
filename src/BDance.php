<?php

namespace Byte\Pay;

class BDance extends TTPay
{
    protected $accessToken = 'https://developer.toutiao.com/api/apps/v2/token';

    protected $session = 'https://developer.toutiao.com/api/apps/v2/jscode2session';

    /**
     * @return array
     */
    public function getToken()
    {
        $data = [
            'appid' => Config::$APP_ID,
            'secret' => Config::$SECRET,
            'grant_type' => 'client_credential'
        ];

        $result = $this->post($this->accessToken, json_encode($data), ['content-type:application/json']);

        return $this->verifyResult($result);
    }

    public function getSession(array $params)
    {
        $this->authSessionParams($params);
        $params = array_merge($params, [
            'appid' => Config::$APP_ID,
            'secret' => Config::$SECRET
        ]);

        $result = $this->post($this->session, json_encode($params), ['content-type:application/json']);

        return $this->verifyResult($result);
    }

    protected function authSessionParams(array $params)
    {
        if (!array_key_exists('code', $params) && !array_key_exists('anonymous_code', $params)) throw new \Exception('参数异常');
    }
}
