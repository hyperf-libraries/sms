<?php

declare(strict_types=1);
/**
 * 螺丝帽
 * @see https://luosimao.com/docs/api/
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

class LuosimaoGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const ENDPOINT_TEMPLATE = 'https://%s.luosimao.com/%s/%s.%s';

    const ENDPOINT_VERSION = 'v1';

    const ENDPOINT_FORMAT = 'json';

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
        $endpoint = $this->buildEndpoint('sms-api', 'send');

        $result = $this->post($endpoint, [
            'mobile' => $to->getNumber(),
            'message' => $message->getContent($this),
        ], [
            'Authorization' => 'Basic '.base64_encode('api:key-'.$this->config->get('sms.gateways.luosimao.api_key')),
        ]);

        if ($result['error']) {
            throw new GatewayErrorException($result['msg'], $result['error'], $result);
        }

        return $result;
    }

    /**
     * Build endpoint url.
     *
     * @param string $type
     * @param string $function
     *
     * @return string
     */
    protected function buildEndpoint($type, $function)
    {
        return sprintf(self::ENDPOINT_TEMPLATE, $type, self::ENDPOINT_VERSION, $function, self::ENDPOINT_FORMAT);
    }
}
