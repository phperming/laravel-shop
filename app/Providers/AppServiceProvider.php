<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //往服务容器中注入名为 alipay 的一个单例对象
        $this->app->singleton('alipay',function(){
            $config = config('pay.alipay');
            $config['notify_url'] = 'payment.alipay.notufy';
            $config['return_url'] = 'payment.alipay.return';
            //判断当前环境是否为线上环境
            if(app()->environment() !== 'production'){
                $config['mode']  ='dev';
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }

            //调用Yansongda/pay来创建一个支付宝支付对象
            return Pay::alipay($config);
        });

        $this->app->singleton('wechat_pay',function(){
            if(app()->environment() !== 'production'){
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }

            return Pay::wechat($config);
        });

        
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
