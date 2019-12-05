<?php

declare(strict_types=1);
/**
 * 阿凡达数据
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

/**
 * Class AvatardataGateway.
 *
 * @see http://www.avatardata.cn/Docs/Api/fd475e40-7809-4be7-936c-5926dd41b0fe
 */
class AvatardataGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'http://v1.avatardata.cn/Sms/Send';

    const ENDPOINT_FORMAT = 'json';

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
        $params = [
            'mobile' => $to->getNumber(),
            'templateId' => $message->getTemplate($this),
            'param' => implode(',', $message->getData($this)),
            'dtype' => self::ENDPOINT_FORMAT,
            'key' => $this->config->get('sms.gateways.avatardata.app_key'),
        ];

        $result = $this->get(self::ENDPOINT_URL, $params);

        if ($result['error_code']) {
            throw new GatewayErrorException($result['reason'], $result['error_code'], $result);
        }

        return $result;
    }
}
