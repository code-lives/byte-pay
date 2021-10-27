<?php

namespace Byte\Pay;

class Split extends TTPay
{
    protected $splitUrl = 'https://developer.toutiao.com/api/apps/ecpay/v1/settle';

    protected $queryUrl = 'https://developer.toutiao.com/api/apps/ecpay/v1/query_settle';

    /**
     * @param array $params
     * @return array
     */
    public function split(array $params)
    {
        $this->authSplitParams($params);

        $params = array_merge($params, ['app_id' => Config::$APP_ID]);

        $result = $this->post($this->splitUrl, json_encode(['sign' => $this->sign($params)] + $params));

        return $this->verifyResult($result);
    }

    protected function authSplitParams(array $params)
    {
        if (!array_key_exists('out_settle_no', $params)) throw new \Exception('缺少参数：out_settle_no');

        if (!array_key_exists('out_order_no', $params)) throw new \Exception('缺少参数：out_order_no');

        if (!array_key_exists('settle_desc', $params)) throw new \Exception('缺少参数：settle_desc');
    }

    /**
     * @param array $params
     * @return array
     */
    public function query(array $params)
    {
        $this->verifyQueryParams($params);

        $params = array_merge($params, ['app_id' => Config::$APP_ID]);

        $result = $this->post($this->queryUrl, json_encode(['sign' => $this->sign($params)] + $params));

        return $this->verifyResult($result);
    }

    public function verifyQueryParams(array $params)
    {
        if (!array_key_exists('out_settle_no', $params)) throw new \Exception('缺少参数：out_settle_no');
    }
}
