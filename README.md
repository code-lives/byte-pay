# 头条小程序担保支付

[官方接口文档](https://microapp.bytedance.com/docs/zh-CN/mini-app/develop/server/ecpay/introduction/)


# Config 参数
 | 参数名字    | 类型   | 必须 | 说明                  |
 | ----------- | ------ | ---- | --------------------- |
 | token       | string | 是   | 担保交易的Token(令牌) |
 | salt        | string | 是   | 担保交易的SALT        |
 | merchant_id | string | 是   | 担保交易的商户号      |
 | app_id      | int    | 是   | 小程序的APP_ID        |
 | secret      | string | 是   | 小程序的APP_SECRET    |

## Config
```php
	 $config = [
        'token' => '',
        'salt' => '',
        'merchant_id' => '',
        'app_id' => '',
        'secret' => ''
    ];
```
## 获取token
```php

     $result = (new \Demo\test\Order($config))->getToken();
        if($result['status'] !== true) {
           print_R($result['message']);exit
        }

    echo $result['response']['data'];//token

```
## 支付
```php

    $result = (new \Demo\test\Order($config))->getOrder([

        'out_order_no' => time() . "", // 开发者订单号
        'total_amount' => 0.01, // 支付金额 单位: 分
        'subject' => '测试-tt-支付', // 商品描述
        'body' => '测试支付系统', // 商品详情
        'notify_url' => '' // 自定义回调地址

    ]);
    //下单成功
    if ($result['status'] === true) {
        //返回的订单号
        $order = [
            'order_id' => $result['response']['data']['order_id'],
            'order_token' => $result['response']['data']['order_token']
        ];
    }

```
## 订单查询
```php

    $result = (new \Demo\test\Order($config))->query([
            'out_order_no' => request()->param('order', '') // 商户订单号
        ]);

    if($result['status'] === false) {
        //查询失败
        print_R($result['message']);exit
    }

    if ($result['status'] === true) {
        //返回的订单号
        $order = [
            'out_order_no' => $result['response']['out_order_no'], // 商户订单号
            'order_id' => $result['response']['order_id'],
            'detail' => $result['response']['payment_info']；
    }

```

## 退款
```php

    $result = (new \Demo\test\Refund($config))->refund([
        'out_order_no' => $order,
        'out_refund_no' => $order . 'refund',
        'reason' => '就想退款，咋滴',
        'refund_amount' => 10,//退款金额，单位[分]
        'notify_url' => ''
    ]);

    if($result['status'] === false) {
        //查询失败
        print_R($result['message']);exit
    }
    echo $result['response']['refund_no'];//担保交易服务端退款单号

```

## 异步回调
```php

     if((new \Demo\test\TTPay($config))->callbackAuth($_POST) !== true) {

        return json_encode(['err_no' => -1, 'err_tips' => '签名验证失败']);

     }
    $data = json_decode($_POST['msg']), true);

    switch ($_POST['type']) {
            case 'payment':
                //支付成功操作 开发者 订单号$data['cp_orderno']
                break;
            case 'refund':
                //退款回调操作
                break;
            case 'settle':
                //
                break;
            default:
                return json_encode(['err_no' => -1, 'err_tips' => '事件异常']);
    }

```