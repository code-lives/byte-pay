<?php

namespace Byte\Pay;

class Test
{
    protected $config = [
        'token' => '',
        'salt' => '',
        'merchant_id' => '',
        'app_id' => '',
        'secret' => ''
    ];

    protected $selfOrderId;

    public function __construct()
    {
        $this->selfOrderId = mt_rand(100, 1000) . microtime(true);
    }

    /**
     * 生成预支付订单号和token
     * @return mixed
     * @throws \Exception
     */
    public function createPayOrder()
    {
        return (new Order($this->config))->getOrder([
            'out_order_no' => $this->selfOrderId, // 开发者订单号
            'total_amount' => 10, // 支付金额 单位: 分
            'subject' => '测试-tt-支付', // 商品描述
            'body' => '测试支付系统', // 商品详情
            'valid_time' => time() + 30 * 60, // 订单过期时间 最小15分钟 最大2天
            'cp_extra' => json_encode(['id' => time()]), // 自定义数据
            'notify_url' => '' // 自定义回调地址
        ]);
    }

    /**
     * 查询订单
     * @return mixed
     * @throws \Exception
     */
    public function queryOrder()
    {
        return (new Order($this->config))->query([
            'out_order_no' => mt_rand(100, 1000) . microtime(true) // 商户订单号
        ]);
    }

    /**
     * 退款申请
     * @return mixed
     * @throws \Exception
     */
    public function refund()
    {
        return (new Refund($this->config))->refund([
            'out_order_no' => $this->selfOrderId, // 要退款的商户订单号
            'out_refund_no' => $this->selfOrderId . 'refund', // 退款编号 商户自定义
            'reason' => '退款', // 退款原因
            'refund_amount' => 10, // 退款金额
            'cp_extra' => json_encode(['name' => 'test']), // 自定义数据 回调时会传,
            'notify_url' => '', // 自定义回调地址
        ]);
    }

    /**
     * 退款查询
     * @return array
     * @throws \Exception
     */
    public function refundQuery()
    {
        return (new Refund($this->config))->query([
            'out_refund_no' => $this->selfOrderId . 'refund' // 商户退款编号
        ]);
    }

    /**
     * 分账申请
     * @return array
     * @throws \Exception
     */
    public function settle()
    {
        return (new Split($this->config))->split([
            'out_settle_no' => md5(microtime(true)) . 'settle', // 商户分账结算号
            'out_order_no' => $this->selfOrderId, // 商户订单号
            'settle_desc' => 'lak;sdjflkasjdflksjdf', // 结算描述 最长80个自负
            'settle_params' => json_encode([]), // 其他分账信息,
            'cp_extra' => json_encode([]), // 自定义分账回调数据
            'notify_url' => '', // 自定义回调地址
        ]);
    }

    /**
     * 分账查询
     * @return array
     * @throws \Exception
     */
    public function settleQuery()
    {
        return (new Split($this->config))->query([
            'out_settle_no' => '', // 商户分账结算号
        ]);
    }

    public function callback()
    {

        if ((new TTPay($this->config))->callbackAuth($params) === false) {
            throw new \Exception('签名验证失败');
        }

        switch ($params['type']) {
            case 'payment': // 支付相关回调
                return json(['err_no' => 0, 'err_tips' => 'success']); // 操作成功需要给头条返回的信息
                break;
            case 'refund': // 退款相关回调
                return json(['err_no' => 0, 'err_tips' => 'success']); // 操作成功需要给头条返回的信息
                break;
            case 'settle': // 分账相关回调
                return json(['err_no' => 0, 'err_tips' => 'success']); // 操作成功需要给头条返回的信息
                break;
            default: // 未知数据
                return '数据异常';
        }

        return 'success';
    }
}
