<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\UserAddress;
use App\Models\Order;
use App\Services\OrderService;


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
}
