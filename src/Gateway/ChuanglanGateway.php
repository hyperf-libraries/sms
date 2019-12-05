<?php

declare(strict_types=1);
/**
 * 253云通讯（创蓝）
 * @see https://zz.253.com/v5.html#/api_doc
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;
use HyperfLibraries\Sms\Exception\InvalidArgumentException;

class ChuanglanGateway extends GatewayAbstract
{
    use HasHttpRequest;

    /**
     * URL模板
     */
    const ENDPOINT_URL_TEMPLATE = 'https://%s.253.com/msg/send/json';

    /**
     * 国际短信
     */
    const INT_URL = 'http://intapi.253.com/send/json';

    /**
     * 验证码渠道code.
     */
    const CHANNEL_VALIDATE_CODE = 'smsbj1';

    /**
     * 会员营销渠道code.
     */
    const CHANNEL_PROMOTION_CODE = 'smssh1';

    /**
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     *
     * @return array
     *
     * @throws GatewayErrorException
     * @throws InvalidArgumentException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message)
    {
        $IDDCode = !empty($to->getIDDCode()) ? $to->getIDDCode() : 86;

        $params = [
            'account' => $this->config->get('sms.gateways.chuanglan.account'),
            'password' => $this->config->get('sms.gateways.chuanglan.password'),
            'phone' => $to->getNumber(),
            'msg' => $this->wrapChannelContent($message->getContent($this), $IDDCode),
        ];

        if (86 != $IDDCode) {
            $params['mobile'] = $to->getIDDCode().$to->getNumber();
            $params['account'] = $this->config->get('sms.gateways.chuanglan.intel_account') ?: $this->config->get('sms.gateways.chuanglan.account');
            $params['password'] = $this->config->get('sms.gateways.chuanglan.intel_password') ?: $this->config->get('sms.gateways.chuanglan.password');
        }

        $result = $this->postJson($this->buildEndpoint($IDDCode), $params);

        if (!isset($result['code']) || '0' != $result['code']) {
            throw new GatewayErrorException(\json_encode($result, JSON_UNESCAPED_UNICODE), isset($result['code']) ? $result['code'] : 0, $result);
        }

        return $result;
    }

    /**
     * @param int    $IDDCode
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function buildEndpoint($IDDCode = 86)
    {
        $channel = $this->getChannel($IDDCode);

        if (self::INT_URL === $channel) {
            return $channel;
        }

        return sprintf(self::ENDPOINT_URL_TEMPLATE, $channel);
    }

    /**
     * @param int    $IDDCode
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    protected function getChannel($IDDCode)
    {
        if (86 != $IDDCode) {
            return self::INT_URL;
        }
        $channel = $this->config->get('sms.gateways.chuanglan.channel', self::CHANNEL_VALIDATE_CODE);

        if (!in_array($channel, [self::CHANNEL_VALIDATE_CODE, self::CHANNEL_PROMOTION_CODE])) {
            throw new InvalidArgumentException('Invalid channel for ChuanglanGateway.');
        }

        return $channel;
    }

    /**
     * @param string $content
     * @param Config $config
     * @param int    $IDDCode
     *
     * @return string|string
     *
     * @throws InvalidArgumentException
     */
    protected function wrapChannelContent($content, $IDDCode)
    {
        $channel = $this->getChannel($IDDCode);

        if (self::CHANNEL_PROMOTION_CODE == $channel) {
            $sign = (string) $this->config->get('sms.gateways.chuanglan.sign', '');
            if (empty($sign)) {
                throw new InvalidArgumentException('Invalid sign for ChuanglanGateway when using promotion channel');
            }

            $unsubscribe = (string) $this->config->get('sms.gateways.chuanglan.unsubscribe', '');
            if (empty($unsubscribe)) {
                throw new InvalidArgumentException('Invalid unsubscribe for ChuanglanGateway when using promotion channel');
            }

            $content = $sign.$content.$unsubscribe;
        }

        return $content;
    }
}
