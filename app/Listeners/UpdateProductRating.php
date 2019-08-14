<?php

namespace App\Listeners;

use App\Events\OrderReviewed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use DB;
use App\Models\OrderItem;

// implements ShouldQueue 代表这个事件处理器是异步的
class UpdateProductRating implements ShouldQueue
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
     * @param  OrderReviewed  $event
     * @return void
     */
    public function handle(OrderReviewed $event)
    {
        //通过with提前加载数据,避免N + 1性能问题
        $items = $event->getOrder()->items()->with(['product'])->get();
        foreach($items as $item){
            $result = OrderItem::query()
                        ->where('product_id',$item->product_id)
                        ->whereHas('order',function($query){
                            $query->whereNotNull('paid_at');
                        })
                        ->first([
                            DB::raw('count(*) as review_count'),
                            DB::raw('avg(rating) as rating'),
                        ]);
            $item->update([
                'review_count' => $result['review_count'],
                'rating' => $result['rating'],
            ]);
        }
}
