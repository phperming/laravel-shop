<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function payByAlipay(Order $order,Request $request)
    {
    	//判断订单是否属于当前用户
    	$this->authorize('own',$order);
    	//判断订单是否已经支付或者关闭
    	if($order->paid_at || $order->closed){
    		throw new InvalidRequestException("订单状态不正确");   		
    	}

    	//调用网页支付宝支付
    	return app('alipay')->web([
    		'out_trade_no' => $order->no,
    		'total_amount' => $order->total_amount,
    		'subject' => '支付 Laravle shop 订单：'.$order->no,
    	]);
    }

    //同步回调
    public function alipayReturn()
    {
    	try{
    		//检验提交参数的合法性
    		app('alipay')->verify();
    	}catch(\Exceptions $e){
    		return view('pages.error',['msg'=>'数据不正确']);
    	}

    	return view('pages.success',['msg'=>'付款成功']);
    }

    //异步回调
    public function alipayNotify()
    {
    	$data = app('alipay')->verify();

    	//如果订单状态不是成功或者结束，则不走下面的逻辑
    	// 所有交易状态：https://docs.open.alipay.com/59/103672
    	if(!in_array($data->trade_status,['TRADE_SUCCESS','TRADE_FINISHED'])){
    		return app('alipay')->success();
    	}

    	//$data->trade_out_no 拿到订单流水单号，在数据库中查询
    	$order = Order::where('no',$data->out_trade_no)->first();

    	if(!$order){
    		return 'fail';
    	}

    	//如果这笔订单是已支付的，返回给支付宝
    	if($order->paid_at){
    		return app('alipay')->success();
    	}

    	$order->update([
    		'paid_at' => Carbon::now(),
    		'payment_method' =>'alipay',
    		'payment_no' => $data->trade_no,
    	]);

    	return app('alipay')->success();
    }
}
