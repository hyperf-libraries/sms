<?php

declare(strict_types=1);
/**
 * 阿里云Rest
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

class AliyunrestGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'http://gw.api.taobao.com/router/rest';

    const ENDPOINT_VERSION = '2.0';

    const ENDPOINT_FORMAT = 'json';

    const ENDPOINT_METHOD = 'alibaba.aliqin.fc.sms.num.send';

    const ENDPOINT_SIGNATURE_METHOD = 'md5';

    const ENDPOINT_PARTNER_ID = 'Sms';

    /**
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     * @param Config               $config
     *
     * @return array|void
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message)
    {
        $urlParams = [
            'app_key' => $this->config->get('sms.gateways.aliyunrest.app_key'),
            'v' => self::ENDPOINT_VERSION,
            'format' => self::ENDPOINT_FORMAT,
            'sign_method' => self::ENDPOINT_SIGNATURE_METHOD,
            'method' => self::ENDPOINT_METHOD,
            'timestamp' => date('Y-m-d H:i:s'),
            'partner_id' => self::ENDPOINT_PARTNER_ID,
        ];

        $params = [
            'extend' => '',
            'sms_type' => 'normal',
            'sms_free_sign_name' => $this->config->get('sms.gateways.aliyunrest.sign_name'),
            'sms_param' => json_encode($message->getData($this)),
            'rec_num' => !is_null($to->getIDDCode()) ? strval($to->getZeroPrefixedNumber()) : $to->getNumber(),
            'sms_template_code' => $message->getTemplate($this),
        ];
        $urlParams['sign'] = $this->generateSign(array_merge($params, $urlParams));

        $result = $this->post($this->getEndpointUrl($urlParams), $params);

        if (isset($result['error_response']) && 0 != $result['error_response']['code']) {
            throw new GatewayErrorException($result['error_response']['msg'], $result['error_response']['code'], $result);
        }

        return $result;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function getEndpointUrl($params)
    {
        return self::ENDPOINT_URL.'?'.http_build_query($params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function generateSign($params)
    {
        ksort($params);

        $stringToBeSigned = $this->config->get('sms.gateways.aliyunrest.app_secret_key');
        foreach ($params as $k => $v) {
            if (!is_array($v) && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->config->get('sms.gateways.aliyunrest.app_secret_key');

        return strtoupper(md5($stringToBeSigned));
    }
}
