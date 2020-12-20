<?php

declare(strict_types=1);
/**
 * hyperf 组件加载配置
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */

namespace HyperfLibraries\Sms;

use HyperfLibraries\Sms\Contract\SmsInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                SmsInterface::class => SmsFactory::class
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for sms component.',
                    'source' => __DIR__ . '/../publish/sms.php',
                    'destination' => BASE_PATH . '/config/autoload/sms.php',
                ],
            ],
        ];
    }
}
