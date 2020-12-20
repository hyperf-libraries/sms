<?php
declare(strict_types=1);
/**
 * Created by PhpStorm
 * User: qingpizi
 * Date: 2020/12/20
 * Time: 上午9:08
 */

namespace HyperfLibraries\Sms\Contract;


interface SmsInterface
{
    /**
     * Send a message.
     *
     * @param string|array                                       $to
     * @param \Overtrue\EasySms\Contracts\MessageInterface|array $message
     * @param array                                              $gateways
     *
     * @return array
     *
     * @throws \Overtrue\EasySms\Exceptions\InvalidArgumentException
     * @throws \Overtrue\EasySms\Exceptions\NoGatewayAvailableException
     */
    public function send($to, $message, array $gateways = []);
}