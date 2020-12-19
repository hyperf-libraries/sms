<?php

declare(strict_types=1);
/**
 * 短信测试用例类
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */
namespace HyperfTest\Cases;
use PHPUnit\Framework\TestCase;
use Hyperf\Guzzle\HandlerStackFactory;
use Overtrue\EasySms\EasySms;

class SmsTest extends TestCase
{
    /**
     * 腾讯短信测试用例
     */
    public function testQcloud()
    {
        $config = ['default' =>
            [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
                // 默认可用的发送网关
                'gateways' => [
                    'qcloud'
                ],
            ],
            // 可用的网关配置
            'gateways' => [
                'qcloud' => [
                    'sdk_app_id' => '', // SDK APP ID
                    'app_key' => '', // APP KEY
                    'sign_name' => '', // 短信签名，如果使用默认签名，该字段可缺省（对应官方文档中的sign）
                ],
                //...
            ],
            'options' => [
                'config' => [
                    'handler' => (new HandlerStackFactory())->create([
                        'min_connections' => 1,
                        'max_connections' => 30,
                        'wait_timeout' => 3.0,
                        'max_idle_time' => 60,
                    ]),
                ],
            ]
        ];
        $easySms = new EasySms($config);

        $result = $easySms->send(18888888, [
            'content'  => '{1}为您的登录验证码，请于5分钟内填写',
            'template' => '12345',
            'data' => [
                'code' => 1234
            ],
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('qcloud', $result);
        $this->assertArrayHasKey('status', $result['qcloud']);
        $this->assertEquals($result['qcloud']['status'], 'success');
    }

}