<?php

declare(strict_types=1);
/**
 * 云之讯
 * @see http://docs.ucpaas.com/doku.php?id=%E7%9F%AD%E4%BF%A1:sendsms
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

class YunzhixunGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const SUCCESS_CODE = '000000';

    const FUNCTION_SEND_SMS = 'sendsms';

    const FUNCTION_BATCH_SEND_SMS = 'sendsms_batch';

    const ENDPOINT_TEMPLATE = 'https://open.ucpaas.com/ol/%s/%s';

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

        $function = isset($data['mobiles']) ? self::FUNCTION_BATCH_SEND_SMS : self::FUNCTION_SEND_SMS;

        $endpoint = $this->buildEndpoint('sms', $function);

        $params = $this->buildParams($to, $message);

        return $this->execute($endpoint, $params);
    }

    /**
     * @param $resource
     * @param $function
     *
     * @return string
     */
    protected function buildEndpoint($resource, $function)
    {
        return sprintf(self::ENDPOINT_TEMPLATE, $resource, $function);
    }

    /**
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     * @param Config               $config
     *
     * @return array
     */
    protected function buildParams(PhoneNumberInterface $to, MessageInterface $message)
    {
        $data = $message->getData($this);

        return [
            'sid' => $this->config->get('sms.gateways.yunzhixun.sid'),
            'token' => $this->config->get('sms.gateways.yunzhixun.token'),
            'appid' => $this->config->get('sms.gateways.yunzhixun.app_id'),
            'templateid' => $message->getTemplate($this),
            'uid' => isset($data['uid']) ? $data['uid'] : '',
            'param' => isset($data['params']) ? $data['params'] : '',
            'mobile' => isset($data['mobiles']) ? $data['mobiles'] : $to->getNumber(),
        ];
    }

    /**
     * @param $endpoint
     * @param $params
     *
     * @return array
     *
     * @throws GatewayErrorException
     */
    protected function execute($endpoint, $params)
    {
        try {
            $result = $this->postJson($endpoint, $params);

            if (!isset($result['code']) || self::SUCCESS_CODE !== $result['code']) {
                $code = isset($result['code']) ? $result['code'] : 0;
                $error = isset($result['msg']) ? $result['msg'] : json_encode($result, JSON_UNESCAPED_UNICODE);

                throw new GatewayErrorException($error, $code);
            }

            return $result;
        } catch (\Exception $e) {
            throw new GatewayErrorException($e->getMessage(), $e->getCode());
        }
    }
}
