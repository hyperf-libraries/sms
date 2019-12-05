<?php

declare(strict_types=1);
/**
 * 按照默认服务商配置顺序进行发短信
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Strategy;

use HyperfLibraries\Sms\Contract\StrategyInterface;

class OrderStrategy implements StrategyInterface
{
    /**
     * Apply the strategy and return result.
     *
     * @param array $gateways
     *
     * @return array
     */
    public function apply(array $gateways)
    {
        return $gateways;
    }
}