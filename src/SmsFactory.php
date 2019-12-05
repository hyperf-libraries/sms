<?php

declare(strict_types=1);
/**
 * 支持hyperf 容器映射工程类
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms;

use Psr\Container\ContainerInterface;
use HyperfLibraries\Sms\Contract\SmsInterface;
use HyperfLibraries\Sms\Exception\InvalidArgumentException;

class SmsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $factory = $container->get(Sms::class);
        if (! ($factory instanceof SmsInterface) ) {
            throw new InvalidArgumentException('on implements SmsInterface');
        }
        return $factory;
    }
}