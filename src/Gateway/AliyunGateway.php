<?php

declare(strict_types=1);
/**
 * 阿里云短信服务
 * @see https://help.aliyun.com/document_detail/55451.html
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Gateway;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\HasHttpRequest;
use HyperfLibraries\Sms\Exception\GatewayErrorException;

class AliyunGateway extends GatewayAbstract
{
    use HasHttpRequest;

    const ENDPOINT_URL = 'http://dysmsapi.aliyuncs.com';

    const ENDPOINT_METHOD = 'SendSms';

    const ENDPOINT_VERSION = '2017-05-25';

    const ENDPOINT_FORMAT = 'JSON';

    const ENDPOINT_REGION_ID = 'cn-hangzhou';

    const ENDPOINT_SIGNATURE_METHOD = 'HMAC-SHA1';

    const ENDPOINT_SIGNATURE_VERSION = '1.0';

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
        $data = $message->getData($this);

        $signName = !empty($data['sign_name']) ? $data['sign_name'] : $this->config->get('sms.gateways.aliyun.sign_name');

        unset($data['sign_name']);

        $params = [
            'RegionId' => self::ENDPOINT_REGION_ID,
            'AccessKeyId' => $this->config->get('sms.gateways.aliyun.access_key_id'),
            'Format' => self::ENDPOINT_FORMAT,
            'SignatureMethod' => self::ENDPOINT_SIGNATURE_METHOD,
            'SignatureVersion' => self::ENDPOINT_SIGNATURE_VERSION,
            'SignatureNonce' => uniqid(),
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'Action' => self::ENDPOINT_METHOD,
            'Version' => self::ENDPOINT_VERSION,
            'PhoneNumbers' => !is_null($to->getIDDCode()) ? strval($to->getZeroPrefixedNumber()) : $to->getNumber(),
            'SignName' => $signName,
            'TemplateCode' => $message->getTemplate($this),
            'TemplateParam' => json_encode($data, JSON_FORCE_OBJECT),
        ];

        $params['Signature'] = $this->generateSign($params);

        $result = $this->get(self::ENDPOINT_URL, $params);

        if ('OK' != $result['Code']) {
            throw new GatewayErrorException($result['Message'], $result['Code'], $result);
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
        ksort($params);
        $accessKeySecret = $this->config->get('sms.gateways.aliyun.access_key_secret');
        $stringToSign = 'GET&%2F&'.urlencode(http_build_query($params, '', '&', PHP_QUERY_RFC3986));

        return base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret.'&', true));
    }
}
