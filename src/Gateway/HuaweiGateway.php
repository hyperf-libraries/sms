<?php

declare(strict_types=1);
/**
 * 华为云 SMS
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use GuzzleHttp\Exception\RequestException;
use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;
use HyperfLibraries\Sms\Exception\InvalidArgumentException;

class HuaweiGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const ENDPOINT_HOST = 'https://api.rtc.huaweicloud.com:10443';

    const ENDPOINT_URI = '/sms/batchSendSms/v1';

    const SUCCESS_CODE = '000000';

    /**
     * 发送信息.
     *
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     * @param Config               $config
     *
     * @return array
     *
     * @throws GatewayErrorException
     * @throws InvalidArgumentException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message)
    {
        $appKey = $this->config->get('sms.gateways.huawei.app_key');
        $appSecret = $this->config->get('sms.gateways.huawei.app_secret');
        $channels = $this->config->get('sms.gateways.huawei.from');
        $statusCallback = $this->config->get('sms.gateways.huawei.callback', '');

        $endpoint = $this->getEndpoint();
        $headers = $this->getHeaders($appKey, $appSecret);

        $templateId = $message->getTemplate($this);
        $messageData = $message->getData($this);

        // 短信签名通道号码
        $from = 'default';
        if (isset($messageData['from'])) {
            $from = $messageData['from'];
            unset($messageData['from']);
        }
        $channel = isset($channels[$from]) ? $channels[$from] : '';

        if (empty($channel)) {
            throw new InvalidArgumentException("From Channel [{$from}] Not Exist");
        }

        $params = [
            'from' => $channel,
            'to' => $to->getUniversalNumber(),
            'templateId' => $templateId,
            'templateParas' => json_encode($messageData),
            'statusCallback' => $statusCallback,
        ];

        try {
            $result = $this->request('post', $endpoint, [
                'headers' => $headers,
                'form_params' => $params,
                //为防止因HTTPS证书认证失败造成API调用失败，需要先忽略证书信任问题
                'verify' => false,
            ]);
        } catch (RequestException $e) {
            $result = $this->unwrapResponse($e->getResponse());
        }

        if (self::SUCCESS_CODE != $result['code']) {
            throw new GatewayErrorException($result['description'], ltrim($result['code'], 'E'), $result);
        }

        return $result;
    }

    /**
     * 构造 Endpoint.
     *
     * @param Config $config
     *
     * @return string
     */
    protected function getEndpoint()
    {
        $endpoint = rtrim($this->config->get('sms.gateways.huawei.endpoint', self::ENDPOINT_HOST), '/');

        return $endpoint.self::ENDPOINT_URI;
    }

    /**
     * 获取请求 Headers 参数.
     *
     * @param string $appKey
     * @param string $appSecret
     *
     * @return array
     */
    protected function getHeaders($appKey, $appSecret)
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'WSSE realm="SDP",profile="UsernameToken",type="Appkey"',
            'X-WSSE' => $this->buildWsseHeader($appKey, $appSecret),
        ];
    }

    /**
     * 构造X-WSSE参数值
     *
     * @param string $appKey
     * @param string $appSecret
     *
     * @return string
     */
    protected function buildWsseHeader($appKey, $appSecret)
    {
        $now = date('Y-m-d\TH:i:s\Z');
        $nonce = uniqid();
        $passwordDigest = base64_encode(hash('sha256', ($nonce.$now.$appSecret)));

        return sprintf('UsernameToken Username="%s",PasswordDigest="%s",Nonce="%s",Created="%s"',
            $appKey, $passwordDigest, $nonce, $now);
    }
}
