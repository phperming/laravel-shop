<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

//implements ShouldQueue 代表异步监听器
class SendOrderPaidMail implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * 
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        //从事件对象中取出相应的订单
        $order = $event->getOrder();
        //调用notify来发送通知
        $order->user->notify(new OrderPaidNotification($order));
    }
}
