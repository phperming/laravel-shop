<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;

class CartController extends Controller
{

	public function index(Request $request)
	{
		$cartItems = $request->user()->cartItems()->with(['productSku.product'])->get();

		return view('cart.index',['cartItems'=>$cartItems]);
	}

    public function add(AddCartRequest $request)
    {
    	$user = $request->user();
    	$skuId = $request->input('sku_id');
    	$amount = $request->input('amount');

    	//从数据库中查询该商品是否在购物车中
    	if($cart = $user->cartItems()->where('product_sku_id',$skuId)->first()){
    		//如果存在，则则直接叠加商品数量
    		$cart->update([
    			'amount' => $cart->amount + $amount,
    		]);
    	}else{
    		//否则创建一个新的购物车记录
    		$cart = new CartItem(['amount'=>$amount]);
    		$cart->user()->associate($user);
    		$cart->productSku()->associate($skuId);
    		$cart->save();
    	}

    	return [];
    }

    public function remove(Request $request,productSku $sku)
    {
    	$request->user()->CartItem()->where('product_sku_id',$sku->id)->delete();

    	return [];
    }
}
