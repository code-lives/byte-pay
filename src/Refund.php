<?php

namespace Byte\Pay;

class Refund extends TTPay
{
    /**
     * @var string
     */
    protected $refundUrl = 'https://developer.toutiao.com/api/apps/ecpay/v1/create_refund';

    protected $queryUrl = 'https://developer.toutiao.com/api/apps/ecpay/v1/query_refund';

    /**
     * @param array $params
     * @return mixed
     */
    public function refund(array $params)
    {
        $this->authRefundParams($params);

        $params = array_merge($params, [
            'app_id' => Config::$APP_ID
        ]);

        $result = $this->post($this->refundUrl, json_encode(['sign' => $this->sign($params)] + $params));

        return $this->verifyResult($result);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    protected function authRefundParams(array $params)
    {
        if (!array_key_exists('out_order_no', $params)) throw new \Exception('缺少参数：out_order_no');

        if (!array_key_exists('out_refund_no', $params)) throw new \Exception('缺少参数：out_refund_no');

        if (!array_key_exists('refund_amount', $params)) throw new \Exception('缺少参数：refund_amount');
    }

    /**
     * @param array $params
     * @return array
     */
    public function query(array $params)
    {
        $this->authQueryParams($params);

        $params = array_merge($params, ['app_id' => Config::$APP_ID]);

        $result = $this->post($this->queryUrl, json_encode(['sign' => $this->sign($params)] + $params));

        return $this->verifyResult($result);
    }

    /**
     * @param array $params
     * @throws \Exception
     */
    protected function authQueryParams(array $params)
    {
        if (!array_key_exists('out_refund_no', $params)) throw new \Exception('缺少参数：out_refund_no');
    }
}
