<?php

declare(strict_types=1);
/**
 * 发送短信接口类
 *
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Contract;

use Closure;
use HyperfLibraries\Sms\Contract\MessageInterface;
use HyperfLibraries\Sms\Exception\InvalidArgumentException;
use HyperfLibraries\Sms\Exception\NoGatewayAvailableException;

interface SmsInterface
{

    /**
     * 发送短信接口
     *
     * @param string|array                                       $to
     * @param MessageInterface|array $message
     * @param array                                              $gateways
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws NoGatewayAvailableException
     */
    public function send($to, $message, array $gateways = []);


    /**
     * 注册自定义驱动程序创建者闭包。
     *
     * @param string   $name
     * @param \Closure $callback
     *
     * @return $this
     */
    public function extend($name, Closure $callback);

}