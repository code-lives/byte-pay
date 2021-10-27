<?php

namespace Byte\Pay;

class Order extends TTPay
{
    protected $payUrl = 'https://developer.toutiao.com/api/apps/ecpay/v1/create_order';

    protected $query = 'https://developer.toutiao.com/api/apps/ecpay/v1/query_order';

    /**
     * @param array $params
     * @return mixed
     */
    public function getOrder(array $params)
    {
        $this->authOrderParams($params);
        $params = array_merge($params, ['app_id' => Config::$APP_ID]);
        $params['total_amount'] *= 100;
        $result = $this->post($this->payUrl, json_encode(['sign' => $this->sign($params)] + $params));

        return $this->verifyResult($result);
    }

    protected function authOrderParams(array $params)
    {
        if (!array_key_exists('out_order_no', $params)) throw new \Exception('缺少参数：out_order_no');

        if (!array_key_exists('total_amount', $params)) throw new \Exception('缺少参数：total_amount');

        if (!array_key_exists('valid_time', $params)) throw new \Exception('缺少参数：valid_time');

        if (!array_key_exists('subject', $params)) throw new \Exception('缺少参数：subject');

        if (!array_key_exists('body', $params)) throw new \Exception('缺少参数：body');
    }

    /**
     * @param array $params
     * @param string $url
     * @return mixed
     */
    public function query(array $params, string $url = '')
    {
        $this->authQueryParams($params);
        $url = $url ?: $this->query;

        $params = array_merge($params, ['app_id' => Config::$APP_ID]);

        $result = $this->post($url, json_encode(['sign' => $this->sign($params)] + $params));

        return $this->verifyResult($result);
    }

    public function authQueryParams(array $params)
    {
        if (!array_key_exists('out_order_no', $params)) throw new \Exception('缺少参数：out_order_no');
    }
}
