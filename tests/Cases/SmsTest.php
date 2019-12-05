<?php

declare(strict_types=1);
/**
 * 短信测试用例类
 * @link     http://www.swoole.red
 * @contact  1712715552@qq.com
 */
namespace HyperfTest\Cases;
use Hyperf\Config\Config;
use Hyperf\Di\Container;
use Hyperf\Utils\ApplicationContext;
use PHPUnit\Framework\TestCase;
use HyperfLibraries\Sms\SmsFactory;
use HyperfLibraries\Sms\Sms;
use Mockery;

class SmsTest extends TestCase
{
    /**
     * 腾讯短信测试用例
     */
    public function testQcloud()
    {
        $client = $this->getClient();

        $result = $client->send('', [ //手机号码
            'content'  => '', //短信内容
            'template' => '', //模板id
            'data' => [
                'code' => 6379 //验证码
            ]
        ]);
        $this->assertEquals($result['qcloud']['status'], 'success');
    }

    protected function getClient(){
        $container = Mockery::mock(Container::class);
        ApplicationContext::setContainer($container);

        $config = new Config([
            'sms' => [
                // HTTP 请求的超时时间（秒）
                'timeout' => 5.0,

                // 默认发送配置
                'default' => [
                    // 网关调用策略，默认：顺序调用
                    'strategy' => \HyperfLibraries\Sms\Strategy\OrderStrategy::class,

                    // 默认可用的发送网关
                    'gateways' => [
                        'qcloud',
                    ],
                ],
                // 可用的网关配置
                'gateways' => [
                    'qcloud' => [
                        'sdk_app_id' => '', // SDK APP ID
                        'app_key' => '', // APP KEY
                        'sign_name' => '', // 短信签名，如果使用默认签名，该字段可缺省（对应官方文档中的sign）
                    ],
                ],
            ],
        ]);

        $container->shouldReceive('get')
            ->once()
            ->with(Sms::class)
            ->andReturn(new Sms($config));

        $factory = new SmsFactory();
        return $factory($container);
    }

    protected function tearDown()
    {
        Mockery::close();
    }
}