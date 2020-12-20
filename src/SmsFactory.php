<?php
declare(strict_types=1);
/**
 * Created by PhpStorm
 * User: qingpizi
 * Date: 2020/12/20
 * Time: 上午9:04
 */

namespace HyperfLibraries\Sms;
use Overtrue\EasySms\EasySms;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\ConfigInterface;

class SmsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class)->get('sms');
        return new EasySms($config);
    }
}