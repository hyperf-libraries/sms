<?php

declare(strict_types=1);
/**
 * 腾讯云短信接口
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

class QcloudGateway extends GatewayAbstract
{
    use HasHttpRequest;
    const ENDPOINT_URL = 'https://yun.tim.qq.com/v5/';
    const ENDPOINT_METHOD = 'tlssmssvr/sendsms';
    /**
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     *
     * @return array
     *
     * @throws GatewayErrorException;
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message)
    {
        $data = $message->getData($this);
        $signName = !empty($data['sign_name']) ? $data['sign_name'] : $this->config->get('sms.gateways.qcloud.sign_name', '');
        unset($data['sign_name']);
        $msg = $message->getContent($this);
        if (!empty($msg) && '【' != mb_substr($msg, 0, 1) && !empty($signName)) {
            $msg = '【'.$signName.'】'.$msg;
        }
        $type = !empty($data['type']) ? $data['type'] : 0;
        $params = [
            'tel' => [
                'nationcode' => $to->getIDDCode() ?: 86,
                'mobile' => $to->getNumber(),
            ],
            'type' => $type,
            'msg' => $msg,
            'time' => time(),
            'extend' => '',
            'ext' => '',
        ];
        if (!is_null($message->getTemplate($this)) && is_array($data)) {
            unset($params['msg']);
            $params['params'] = array_values($data);
            $params['tpl_id'] = $message->getTemplate($this);
            $params['sign'] = $signName;
        }
        $random = substr(uniqid(), -10);
        $params['sig'] = $this->generateSign($params, $random);
        $url = self::ENDPOINT_URL.self::ENDPOINT_METHOD.'?sdkappid='.$this->config->get('sms.gateways.qcloud.sdk_app_id').'&random='.$random;

        $result = $this->request('post', $url, [
            'headers' => ['Accept' => 'application/json'],
            'json' => $params,
        ]);
        if (isset($result['ActionStatus']) && $result['ActionStatus'] === 'FAIL') {
            throw new GatewayErrorException($result['ErrorInfo'], $result['ErrorCode'], $result);
        }
        return $result;
    }
    /**
     * 加密
     *
     * @param array  $params
     * @param string $random
     *
     * @return string
     */
    protected function generateSign($params, $random)
    {
        ksort($params);
        return hash('sha256', sprintf(
            'appkey=%s&random=%s&time=%s&mobile=%s',
            $this->config->get('sms.gateways.qcloud.app_key'),
            $random,
            $params['time'],
            $params['tel']['mobile']
        ), false);
    }
}