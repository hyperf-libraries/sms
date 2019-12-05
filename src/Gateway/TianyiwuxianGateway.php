<?php

declare(strict_types=1);
/**
 * Submail
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

class TianyiwuxianGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const ENDPOINT_TEMPLATE = 'http://jk.106api.cn/sms%s.aspx';

    const ENDPOINT_ENCODE = 'UTF8';

    const ENDPOINT_TYPE = 'send';

    const ENDPOINT_FORMAT = 'json';

    const SUCCESS_STATUS = 'success';

    const SUCCESS_CODE = '0';

    /**
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     *
     * @return array
     *
     * @throws GatewayErrorException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message)
    {
        $endpoint = $this->buildEndpoint();

        $params = [
            'gwid' => $this->config->get('gwid'),
            'type' => self::ENDPOINT_TYPE,
            'rece' => self::ENDPOINT_FORMAT,
            'mobile' => $to->getNumber(),
            'message' => $message->getContent($this),
            'username' => $this->config->get('username'),
            'password' => strtoupper(md5($this->config->get('password'))),
        ];

        $result = $this->post($endpoint, $params);

        $result = json_decode($result, true);

        if (self::SUCCESS_STATUS !== $result['returnstatus'] || self::SUCCESS_CODE !== $result['code']) {
            throw new GatewayErrorException($result['remark'], $result['code']);
        }

        return $result;
    }

    /**
     * Build endpoint url.
     *
     * @return string
     */
    protected function buildEndpoint()
    {
        return sprintf(self::ENDPOINT_TEMPLATE, self::ENDPOINT_ENCODE);
    }
}
