<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\UserAddress;
use App\Models\Order;
use App\Services\OrderService;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;
use App\Http\Requests\sendReviewRequest;
use App\Events\OrderReviewed;
use App\Http\Requests\ApplyRefundRequest;

class OrdersController extends Controller
{

	public function index(Request $request)
	{
		$user = $request->user();
		$orders = Order::query()
					->with(['items.product','items.productSku'])
					->where('user_id',$user->id)
					->orderBy('created_at','desc')
					->paginate();
		return view('orders.index',['orders'=>$orders]);
	}

	//利用laravel 的自动解析功能注入CartService
    public function store(OrderRequest $request,OrderService $orderService)
    {
    	$user = $request->user();
    	$address = UserAddress::find($request->input('address_id'));

    	return $orderService->store($user,$address,$request->input('remark'),$request->input('items'));
    }

    public function show(Order $order,Request $request)
    {
    	$this->authorize('own',$order);
    	return view('orders.show',['order'=>$order->load(['items.product','items.productSku'])]);
    }

    public function  received(Order $order,Request $request)
    {
    	//检验权限
    	$this->authorize('own',$order);

    	//判断订单是否是已发货状态
    	if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
    		throw new InvalidRequestException('该订单未发货');
    	}

    	//更新发货状态为已收货
    	$order->update([
    		'ship_status' => Order::SHIP_STATUS_RECEIVED,
    	]);

    	// 返回订单信息
        return $order;
    }

    public function review(Order $order,Request $request)
    {
    	//检验权限
    	$this->authorize('own',$order);
    	//判断是否已经支付
    	if (!$order->paid_at) {
    		throw new InvalidRequestException('该订单未付款');
    	}

    	//使用load加载关联数据 ，避免N+1问题
    	return view('orders.review',['order'=>$order->load(['items.productSku','items.product'])]);
    }

    public function sendReview(Order $order,sendReviewRequest $request)
    {
    	//权限校验
    	$this->authorize('own',$order);

    	if (!$order->paid_at){
    		throw new InvalidRequestException('该订单未付款');
    	}
    	//判断是否已经评价
    	if($order->reviewed) {
    		throw new InvalidRequestException('该订单已经评论了,不可重复提交');
    	}

    	$reviews = $request->input('reviews');

    	//开启事务
    	\DB::transaction(function() use ($reviews,$order){
    		//遍历用户提交的数据
    		foreach($reviews as $review) {
    			$orderItem = $order->items()->find($review['id']);
    			//保存评分和评价
    			$orderItem->update([
    				'rating' => $review['rating'],
    				'review' => $review['review'],
    				'reviewed_at' => Carbon::now(),
     			]);
    		}

    		// 将订单标记为已评价
    		$order->update([
    			'reviewed' => true,
    		]);

    		event(new OrderReviewed($order));
    	});

    	return redirect()->back();
    }

    public function applyRefund(ApplyRefundRequest $request,Order $order)
    {
    	//校验订单是否属于当前用户
    	$this->authorize('own',$order);
    	//判断订单是否已经付款
    	if (!$order->paid_at) {
    		throw new InvalidRequestException('该订单未付款');
    	}
    	//判断订单退款状态是否正确
    	if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
    		throw new InvalidRequestException('该订单已经申请过退款,不能重复提交');
    	}

    	//将用户的退款原因放到extra字段中
    	$extra = $order->extra ?:[];
    	$extra['refund_reason'] = $request->input('reason');
    	//将订单退款状态修改为已申请退款状态
    	$order->update([
    		'refound_status' => Order::REFUND_STATUS_APPLIED,
    		'extra' => $extra,
    	]);

    	return $order;

    }
    

}
