<?php
declare(strict_types=1);
/**
 * 随机服务商配置顺序进行发短信
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms\Strategy;

use HyperfLibraries\Sms\Contract\StrategyInterface;

class RandomStrategy implements StrategyInterface
{
    /**
     * @param array $gateways
     *
     * @return array
     */
    public function apply(array $gateways)
    {
        shuffle($gateways);
        return $gateways;
    }
}