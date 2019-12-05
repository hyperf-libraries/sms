<?php

declare(strict_types=1);
/**
 * 融云
 * @see http://www.rongcloud.cn/docs/sms_service.html#send_sms_code
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use GuzzleHttp\Exception\ClientException;
use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

class RongcloudGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const ENDPOINT_TEMPLATE = 'http://api.sms.ronghub.com/%s.%s';

    const ENDPOINT_ACTION = 'sendCode';

    const ENDPOINT_FORMAT = 'json';

    const ENDPOINT_REGION = '86';  // 中国区，目前只支持此国别

    const SUCCESS_CODE = 200;

    /**
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     *
     * @return array
     *
     * @throws GatewayErrorException ;
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message)
    {
        $data = $message->getData();
        $action = array_key_exists('action', $data) ? $data['action'] : self::ENDPOINT_ACTION;
        $endpoint = $this->buildEndpoint($action);

        $headers = [
            'Nonce' => uniqid(),
            'App-Key' => $this->config->get('sms.gateways.rongcloud.app_key'),
            'Timestamp' => time(),
        ];
        $headers['Signature'] = $this->generateSign($headers);

        switch ($action) {
            case 'sendCode':
                $params = [
                    'mobile' => $to->getNumber(),
                    'region' => self::ENDPOINT_REGION,
                    'templateId' => $message->getTemplate($this),
                ];

                break;
            case 'verifyCode':
                if (!array_key_exists('code', $data)
                    or !array_key_exists('sessionId', $data)) {
                    throw new GatewayErrorException('"code" or "sessionId" is not set', 0);
                }
                $params = [
                    'code' => $data['code'],
                    'sessionId' => $data['sessionId'],
                ];

                break;
            case 'sendNotify':
                $params = [
                    'mobile' => $to->getNumber(),
                    'region' => self::ENDPOINT_REGION,
                    'templateId' => $message->getTemplate($this),
                    ];
                $params = array_merge($params, $data);

                break;
            default:
                throw new GatewayErrorException(sprintf('action: %s not supported', $action));
        }

        try {
            $result = $this->post($endpoint, $params, $headers);

            if (self::SUCCESS_CODE !== $result['code']) {
                throw new GatewayErrorException($result['errorMessage'], $result['code'], $result);
            }
        } catch (ClientException $e) {
            throw new GatewayErrorException($e->getMessage(), $e->getCode());
        }

        return $result;
    }

    /**
     * Generate Sign.
     *
     * @param array                            $params
     *
     * @return string
     */
    protected function generateSign($params)
    {
        return sha1(sprintf('%s%s%s', $this->config->get('sms.gateways.rongcloud.app_secret'), $params['Nonce'], $params['Timestamp']));
    }

    /**
     * Build endpoint url.
     *
     * @param string $action
     *
     * @return string
     */
    protected function buildEndpoint($action)
    {
        return sprintf(self::ENDPOINT_TEMPLATE, $action, self::ENDPOINT_FORMAT);
    }
}
