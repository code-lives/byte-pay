<?php

namespace Byte\Pay;

/**
 *
 */
class TTPay
{
    /**
     * @var int
     */
    public $timeout = 30;

    protected $request = [];

    /**
     * @param array $config
     * @throws \Exception
     */
    public function __construct(array $config = [])
    {
        if (!array_key_exists('app_id', $config)) throw new \Exception('缺少配置参数：app_id');

        if (!array_key_exists('secret', $config)) throw new \Exception('缺少配置参数：secret');

        if (!array_key_exists('merchant_id', $config)) throw new \Exception('缺少商户号配置：merchant_id');

        if (!array_key_exists('salt', $config)) throw new \Exception('缺少配置参数：salt');

        Config::$APP_ID = $config['app_id'];
        Config::$SECRET = $config['secret'];
        Config::$MERCHANT_ID = $config['merchant_id'];
        Config::$SALT = $config['salt'];
        Config::$TOKEN = array_key_exists('token', $config) ? $config['token'] : '';
    }

    /**
     * @param array $map
     * @return string
     */
    public function sign(array $map)
    {
        $rList = array();
        foreach ($map as $k => $v) {
            if ($k == "other_settle_params" || $k == "app_id" || $k == "sign" || $k == "thirdparty_id")
                continue;
            $value = trim(strval($v));
            $len = strlen($value);
            if ($len > 1 && substr($value, 0, 1) == "\"" && substr($value, $len, $len - 1) == "\"")
                $value = substr($value, 1, $len - 1);
            $value = trim($value);
            if ($value == "" || $value == "null")
                continue;
            array_push($rList, $value);
        }
        array_push($rList, Config::$SALT);
        sort($rList, 2);
        return md5(implode('&', $rList));
    }

    /**
     * @param array $params
     * @return string
     */
    public function callbackSign(array $params)
    {
        $data = [
            $params['timestamp'],
            (string) $params['nonce'],
            (string) $params['msg'],
            (string) Config::$TOKEN
        ];

        sort($data, SORT_STRING);

        return hash('sha1', join('', $data));
    }

    /**
     * @param array $callbackData
     * @return bool
     */
    public function callbackAuth(array $callbackData)
    {
        $callbackSign = data_get($callbackData, 'msg_signature', data_get($callbackData, 'signature', ''));
        return $callbackSign === $this->callbackSign($callbackData);
    }

    /**
     * @param string $url
     * @param array $options
     * @return bool|string
     */
    protected function curl(string $url, array $options = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $this->request[] = $this->getCurlRequestInfo($ch);
        curl_close($ch);
        return $result;
    }

    protected function getCurlRequestInfo($ch)
    {
        return [
            'info' => curl_getinfo($ch),
            'error' => [
                'errno' => curl_errno($ch),
                'error' => curl_error($ch)
            ]
        ];
    }

    /**
     * @param array $options
     * @return array
     */
    public function options(array $options = [])
    {
        return $options + [
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT        => $this->timeout
        ];
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $header
     * @return mixed
     */
    public function post(string $uri, $data = [], array $header = [])
    {
        $options                        = $this->options();
        $options[CURLOPT_HTTPHEADER]    = $header;
        $options[CURLOPT_CUSTOMREQUEST] = 'POST';
        $options[CURLOPT_POSTFIELDS]    = $data;
        return $this->curl($uri, $options);
    }

    protected function error($message, $data = [])
    {
        return [
            'status' => false,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * @param string $result
     * @return array
     */
    protected function verifyResult($result)
    {
        if ($result === false) {
            $request = end($this->request);
            return $this->error($request['error']['error'], $request);
        }

        $body = json_decode($result, true);
        if (json_last_error() || empty($body)) {
            return $this->error(json_last_error_msg(), $result);
        }

        if (!isset($body['err_no'])) {
            return $this->error('响应数据格式错误', $result);
        }

        if ($body['err_no'] != 0) {
            return $this->error(isset($body['err_tips']) ? $body['err_tips'] : '响应处理失败', $result);
        }

        return ['status' => true, 'message' => 'success', 'response' => $body, 'resource' => $result];
    }

    protected function verifyResultV2($result)
    {
        if ($result === false) {
            $request = end($this->request);
            return $this->error($request['error']['error'], $request);
        }

        $body = json_decode($result, true);
        if (json_last_error() || empty($body)) {
            return $this->error(json_last_error_msg(), $result);
        }

        if (!isset($body['error'])) {
            return $this->error('响应数据格式错误', $result);
        }

        if ($body['error'] != 0) {
            return $this->error(isset($body['message']) ? $body['message'] : '响应处理失败', $result);
        }

        return ['status' => true, 'message' => 'success', 'response' => $body, 'resource' => $result];
    }
}
