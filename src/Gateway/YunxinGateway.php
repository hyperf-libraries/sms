<?php

declare(strict_types=1);
/**
 * 网易云信
 * @see https://dev.yunxin.163.com/docs/product/%E7%9F%AD%E4%BF%A1/%E7%9F%AD%E4%BF%A1%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

class YunxinGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const ENDPOINT_TEMPLATE = 'https://api.netease.im/%s/%s.action';

    const ENDPOINT_ACTION = 'sendCode';

    const SUCCESS_CODE = 200;

    /**
     * Send a short message.
     *
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     *
     * @return array
     *
     * @throws GatewayErrorException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message)
    {
        $data = $message->getData($this);

        $action = isset($data['action']) ? $data['action'] : self::ENDPOINT_ACTION;

        $endpoint = $this->buildEndpoint('sms', $action);

        switch ($action) {
            case 'sendCode':
                $params = $this->buildSendCodeParams($to, $message);

                break;
            case 'verifyCode':
                $params = $this->buildVerifyCodeParams($to, $message);

                break;
            default:
                throw new GatewayErrorException(sprintf('action: %s not supported', $action), 0);
        }

        $headers = $this->buildHeaders();

        try {
            $result = $this->post($endpoint, $params, $headers);

            if (!isset($result['code']) || self::SUCCESS_CODE !== $result['code']) {
                $code = isset($result['code']) ? $result['code'] : 0;
                $error = isset($result['msg']) ? $result['msg'] : json_encode($result, JSON_UNESCAPED_UNICODE);

                throw new GatewayErrorException($error, $code);
            }
        } catch (\Exception $e) {
            throw new GatewayErrorException($e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * @param $resource
     * @param $function
     *
     * @return string
     */
    protected function buildEndpoint($resource, $function)
    {
        return sprintf(self::ENDPOINT_TEMPLATE, $resource, strtolower($function));
    }

    /**
     * Get the request headers.
     *
     *
     * @return array
     */
    protected function buildHeaders()
    {
        $headers = [
            'AppKey' => $this->config->get('app_key'),
            'Nonce' => md5(uniqid('easysms')),
            'CurTime' => (string) time(),
            'Content-Type' => 'application/x-www-form-urlencoded;charset=utf-8',
        ];

        $headers['CheckSum'] = sha1("{$this->config->get('sms.gateways.yunxin.app_secret')}{$headers['Nonce']}{$headers['CurTime']}");

        return $headers;
    }

    /**
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     *
     * @return array
     */
    public function buildSendCodeParams(PhoneNumberInterface $to, MessageInterface $message)
    {
        $data = $message->getData($this);
        $template = $message->getTemplate($this);

        return [
            'mobile' => $to->getUniversalNumber(),
            'authCode' => array_key_exists('code', $data) ? $data['code'] : '',
            'deviceId' => array_key_exists('device_id', $data) ? $data['device_id'] : '',
            'templateid' => is_string($template) ? $template : '',
            'codeLen' => $this->config->get('sms.gateways.yunxin.code_length', 4),
            'needUp' => $this->config->get('sms.gateways.yunxin.need_up', false),
        ];
    }

    /**
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     *
     * @return array
     *
     * @throws GatewayErrorException
     */
    public function buildVerifyCodeParams(PhoneNumberInterface $to, MessageInterface $message)
    {
        $data = $message->getData($this);

        if (!array_key_exists('code', $data)) {
            throw new GatewayErrorException('"code" cannot be empty', 0);
        }

        return [
            'mobile' => $to->getUniversalNumber(),
            'code' => $data['code'],
        ];
    }
}
