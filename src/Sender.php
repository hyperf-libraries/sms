<?php

declare(strict_types=1);
/**
 * 手机号码实现类
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\Exception\NoGatewayAvailableException;

class Sender
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILURE = 'failure';
    /**
     * @var \HyperfLibraries\Sms\Sms
     */
    protected $sms;

    /**
     * Messenger constructor.
     *
     * @param Sms $sms
     */
    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    /**
     * Send a message.
     *
     * @param PhoneNumberInterface $to
     * @param MessageInterface $message
     * @param array $gateways
     *
     * @return array
     *
     * @throws \HyperfLibraries\Sms\Exception\NoGatewayAvailableException
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message, array $gateways)
    {
        $results      = [];
        $isSuccessful = false;
        foreach ($gateways as $gateway) {
            try {
                $results[$gateway] = [
                    'gateway' => $gateway,
                    'status'  => self::STATUS_SUCCESS,
                    'result'  => $this->sms->gateway($gateway)->send($to, $message),
                ];
                $isSuccessful      = true;
                break;
            } catch (\Exception $e) {
                $results[$gateway] = [
                    'gateway'   => $gateway,
                    'status'    => self::STATUS_FAILURE,
                    'exception' => $e,
                ];
            } catch (\Throwable $e) {
                $results[$gateway] = [
                    'gateway'   => $gateway,
                    'status'    => self::STATUS_FAILURE,
                    'exception' => $e,
                ];
            }
        }
  
        if (!$isSuccessful) {
            throw new NoGatewayAvailableException($results);
        }
        return $results;
    }
}