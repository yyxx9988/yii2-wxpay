<?php

namespace yyxx9988\wxpay;

use yii\httpclient\Client;

class Unifiedorder extends Wxpay implements UnifiedorderInterface
{
    /**
     * 必须存在的属性
     */
    private $_requiredAttrs = [
        'appid' => 'appId',
        'openid' => 'openId',
        'mch_id' => 'mchId',
        'nonce_str' => 'nonceStr',
        'body' => 'body',
        'out_trade_no' => 'outTradeNo',
        'total_fee' => 'totalFee',
        'spbill_create_ip' => 'userIp',
        'notify_url' => 'notifyUrl',
        'trade_type' => 'tradeType',

        'limit_pay' => 'limitPay',
        'device_info' => 'deviceInfo',
        'sign_type' => 'signType',
        'fee_type' => 'feeType',
    ];

    private $_order = [];

    /**
     * 初始化
     * @throws Exception
     */
    public function init()
    {
        parent::init();
        foreach ($this->_requiredAttrs as $k => $v) {
            if (!$this->$v) {
                throw new Exception("属性{$v}缺失");
            } else {
                $this->_order[$k] = $this->$v;
            }
        }
//        $this->_order['time_start'] = $this->timeStamp;
//        $this->_order['time_expire'] = $this->timeStamp + 50 * 60 + 1;
//        $this->_order['detail'] = \yii\helpers\Json::encode([
//            'cost_price' => $this->totalFee,
//            'goods_detail' => $this->goodsInfo
//        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 创建预支付订单
     * @return bool|null
     */
    public function createOrder()
    {
        $client = new Client([
            'formatters' => [
                Client::FORMAT_XML => [
                    'class' => 'yii\httpclient\XmlFormatter',
                    'rootTag' => 'xml'
                ],
                'requestConfig' => [
                    'format' => Client::FORMAT_XML
                ],
                'responseConfig' => [
                    'format' => Client::FORMAT_XML
                ],
            ],
        ]);

        $result = $client->createRequest()
            ->setMethod('post')
            ->setUrl(self::API_UNIFIEDORDER)
            ->setData($this->_order)
            ->setOptions([
                'timeout' => 24
            ])
            ->send();

        if (!$result->getIsOk()) {
            $this->errmsg = '下单失败：服务器网络异常';
        } else {
            $data = $result->getData();
            if (!isset($data['return_code']) || $data['return_code'] !== 'SUCCESS') {
                $this->errmsg = '下单失败：服务器通信异常' . $this->getError($data);
                return false;
            }
            if (!isset($data['result_code']) || $data['result_code'] !== 'SUCCESS') {
                $this->errmsg = '下单失败：支付业务异常' . $this->getError($data);
                return false;
            }
            if (isset($data['prepay_id'])) {
                $this->unifiedData = $data;
                $this->prepayId = $data['prepay_id'];
                return true;
            } else {
                $this->errmsg = '下单失败：预支付异常' . $this->getError($data);
            }
        }

        return false;
    }

    /**
     * 获取错误信息
     * @param array $data
     * @return string
     */
    private function getError($data = [])
    {
        $code = isset($data['err_code']) ? $data['err_code'] : '';
        $msg = isset($data['return_msg']) ? $data['return_msg'] : '';

        return $code . $msg;
    }
}
