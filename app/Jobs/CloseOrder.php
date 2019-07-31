<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Order;

// 代表这个类需要被放到队列中执行，而不是触发时立即执行
class CloseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order,$delay)
    {
        $this->order = $order;
        //设置延迟时间，delay()方法的参数是代表多久之后执行
        $this->delay($delay);
    }

    /**
     *定义这个任务类具体的执行逻辑
     *当队列处理器从队列中取出任务时，会调用 handle() 方法
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //判断对应的订单是否已支付
        //如果已经支付，则不需要关闭订单，直接退出
        if($this->order->paid_at){
            return ;
        }

        //通过执行事务sql
        \DB::transaction(function(){
            //将订单的closed字段改为true ,即关闭订单
            $this->order->update(['closed' => true]);

            //循环遍历订单中的商品，将订单中的数量加回到SKU的库存中去
            foreach($this->order->items as $item){
                $item->productSku->addStock($item->amount);
            }
        });
    }
}