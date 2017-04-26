<?php

namespace yyxx9988\wxpay;

use Yii;
use yii\base\Component;
use yii\helpers\Html;

class Wxpay extends Component
{
    /**
     * 统一下单接口地址
     */
    const API_UNIFIEDORDER = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    /**
     * @var string 应用ID
     */
    public $appId;
    /**
     * @var string 商户号
     */
    public $mchId;
    /**
     * @var int 以分为单位的总费用
     */
    public $totalFee;
    /**
     * @var string 唯一订单号 商户支付的订单号由商户自定义生成，微信支付要求商户订单号保持唯一性（建议根据当前系统时间加随机序列来生成订单号）。重新发起一笔支付要使用原订单号，避免重复支付；已支付过或已调用关单、撤销（请见后文的API列表）的订单号不能重新发起支付。
     */
    public $outTradeNo;
    /**
     * @var string 用户OpenID
     */
    public $openId;
    /**
     * @var string 用户IP地址
     */
    public $userIp;
    /**
     * @var string 订单标题
     */
    public $body = '启行有方-教育产品';
    /**
     * @var string 接收微信支付异步通知回调地址，通知url必须为直接可访问的url，不能携带参数
     */
    public $notifyUrl;
    /**
     * @var array 二维数组
     * ```php
     * [
     *    [
     *       'goods_id' => '',
     *       'goods_name' => '',
     *       'goods_num' => '',
     *       'price' => '',
     *       'body' => '',
     *    ]
     * ]
     * ```
     */
    public $goodsInfo;
    /**
     * @var string 支付密钥（微信商户平台-->账户设置-->API安全-->密钥设置）
     */
    public $payKey;
    /**
     * @var string 随机字符串
     */
    public $nonceStr;
    /**
     * @var int 当前的时间戳
     */
    public $timeStamp;
    /**
     * @var string 签名方式
     */
    public $signType = 'MD5';
    /**
     * @var string 接口调用方式
     */
    public $tradeType = 'JSAPI';
    /**
     * @var string 设备信息
     */
    public $deviceInfo = 'WEB';
    /**
     * @var string 指定支付方式
     */
    public $limitPay = 'no_credit';

    /**
     * @var string 预支付交易会话标识
     */
    protected $prepayId;
    /**
     * @var string 预支付交易数据
     */
    protected $unifiedData;
    /**
     * @var string 错误消息
     */
    protected $errmsg;

    public function init()
    {
        parent::init();
        if (!$this->nonceStr) {
            $this->nonceStr = Yii::$app->getSecurity()->generateRandomString(16);
        }
        if (!$this->timeStamp) {
            $this->timeStamp = time();
        }
    }

    /**
     * 获取支付配置
     * @return array|null
     */
    public function getConfig()
    {
        /**
         * @var $uo Unifiedorder
         */
        $uo = Yii::createObject('yyxx9988\wxpay\Unifiedorder');
        if ($uo->createOrder()) {
            if ($this->prepayId) {
                $config = [
                    'appId' => $this->appId,
                    'timeStamp' => $this->timeStamp,
                    'nonceStr' => $this->nonceStr,
                    'package' => 'prepay_id=' . $this->prepayId,
                    'signType' => $this->signType
                ];
                $config['paySign'] = $this->generateSign($config);

                return $config;
            }
        }
        return null;
    }

    /**
     * 预支付交易数据
     * @return string
     */
    public function getUnifiedData()
    {
        return $this->unifiedData;
    }

    /**
     * 获取错误消息
     * @return string
     */
    public function getErrmsg()
    {
        return $this->errmsg;
    }

    /**
     * 获取数据签名
     * @param array $fields
     * @return string
     */
    protected function generateSign(array $fields)
    {
        unset($fields['sign']); // sign不参与签名
        ksort($fields);
        $t = '';
        foreach ($fields as $k => $v) {
            $t .= $k . '=' . $v . '&';
        }
        $t .= 'key=' . $this->payKey;
        return strtoupper(md5($t));
    }

    /**
     * 数组array转为xml
     * @param array $array 要处理的数组array
     * @param array $noEncodeKeys 不执行encode的键
     * @return string 成功返回xml
     */
    protected function array2xml(array $array, $noEncodeKeys = [])
    {
        $xml = '<xml>';
        foreach ($array as $k => $v) {
            if (isset($noEncodeKeys[$k])) {
                $xml .= "<{$k}><![CDATA[{$v}]]></{$k}>";
            } else {
                $xml .= "<{$k}><![CDATA[" . Html::encode($v) . "]]></{$k}>";
            }
        }
        $xml .= '</xml>';

        return $xml;
    }
}
