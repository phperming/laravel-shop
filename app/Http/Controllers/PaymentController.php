<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Exceptions\InvalidRequestException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use App\Events\OrderPaid;

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

    public function payByWechat(Order $order,Request $request)
    {
    	//判断订单是否属于当前用户
    	$this->authorize('own',$order);
    	//检验订单状态
    	if($order->closed || $order->paid_at)
    	{
    		throw new InvalidRequestException("订单状态不正确");
    		
    	}

    	$wechatOrder = app('wechat_pay')->san([
    		'out_trade_no' =>$order->no,
    		'total_fee' => $order->total_amount,
    		'body' => '支付Laravel订单:'.$order->no, 
    	]);

    	//把要转换的字符串作物QRcode构造函数的参数
    	$qrCode = new QrCode($wechatOrder->code_url);

    	// 将生成的二维码图片数据以字符串形式输出，并带上相应的响应类型
        return response($qrCode->writeString(), 200, ['Content-Type' => $qrCode->getContentType()]);
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

    	$this->afterPaid($order);

    	return app('alipay')->success();
    }

    // 微信异步回调
    public function webchatNotify()
    {
    	//检验回调参数
    	$data = app('wechat_pay')->verify();

    	//从数据库找到对应的订单
    	$order = Order::where('no',$data->out_trade_no)->first();

    	if(!$order)
    	{
    		return 'fail';
    	}

    	//订单已支付
    	if($order->paid_at)
    	{
    		return app('wechat_pay')->success();
    	}

    	//将订单标记为已支付
    	$order->update([
    		'paid_at' => Carbon::now(),
    		'payment_method' => 'wechat',
    		'payment_no' => $data->transaction_id,

    	]);
    	$this->afterPaid($order);
    	return app('wechat_pay')->success();
    }

    public function wechatRefundNotify(Request $request)
    {
    	//给微信的响应失败
    	$failxml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg>![CDATA[FAIL]]</return_msg></xml>';
    	$data = app('wechat_pay')->verify(null,true);

    	// 没有找到对应的订单，原则上不可能发生，保证代码健壮性
        if(!$order = Order::where('no', $data['out_trade_no'])->first()) {
            return $failXml;
        }

        if ($data['refund_status'] == 'SUCCESS'){
        	//退款成功，将订单状态更改退款成功
        	$order->update([
        		'refund_status' => Order::REFUND_STATUS_SUCCESS,
        	]);
        }else{
        	//退款失败，将具体状态存入extra字段，并将状态改为退款失败
        	$extra = $order->extra;
        	$extra['refund_failed_code'] = $data['refund_status'];

        	//更新退款状态
        	$order->update([
        		'refund_status' => Order::REFUND_STATUS_FAILED,
        		'extra' => $extra,
        	]);

        }

        return app('wechat_pay')->success();
    }
}
