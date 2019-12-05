<?php

declare(strict_types=1);
/**
 * 互亿无线
 * @see http://www.ihuyi.com/api/sms.html
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

class HuyiGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'http://106.ihuyi.com/webservice/sms.php?method=Submit';

    const ENDPOINT_FORMAT = 'json';

    const SUCCESS_CODE = 2;

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
        $params = [
            'account' => $this->config->get('sms.gateways.huyi.api_id'),
            'mobile' => $to->getIDDCode() ? \sprintf('%s %s', $to->getIDDCode(), $to->getNumber()) : $to->getNumber(),
            'content' => $message->getContent($this),
            'time' => time(),
            'format' => self::ENDPOINT_FORMAT,
            'sign' => $this->config->get('sms.gateways.huyi.signature'),
        ];

        $params['password'] = $this->generateSign($params);

        $result = $this->post(self::ENDPOINT_URL, $params);

        if (self::SUCCESS_CODE != $result['code']) {
            throw new GatewayErrorException($result['msg'], $result['code'], $result);
        }

        return $result;
    }

    /**
     * Generate Sign.
     *
     * @param array $params
     *
     * @return string
     */
    protected function generateSign($params)
    {
        return md5($params['account'].$this->config->get('sms.gateways.huyi.api_key').$params['mobile'].$params['content'].$params['time']);
    }
}
