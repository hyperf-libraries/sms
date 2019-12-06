<?php

declare(strict_types=1);
/**
 * 短信服务商网关接口类
 *
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */
namespace HyperfLibraries\Sms\Contract;

use HyperfLibraries\Sms\Contract\PhoneNumberInterface;
use HyperfLibraries\Sms\Contract\MessageInterface;

interface GatewayInterface
{
    /**
     * 获取服务商名称
     *
     * @return string
     */
    public function getName();
    /**
     * 发短信
     *
     * @param PhoneNumberInterface $to
     * @param MessageInterface     $message
     *
     * @return array
     */
    public function send(PhoneNumberInterface $to, MessageInterface $message);
}