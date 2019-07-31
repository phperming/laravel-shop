<?php

namespace App\Services;

use Auth;
use App\Models\CartItem;

class CartService 
{
	public function get()
	{
		return Auth::user()->Cartitems()->with(['productSku.product'])->get();
	}


	public function add($skuId,$amount)
	{
		$user = Auth::user();
		//从数据库中查询该商品是否已经存在购物车
		if($cart = $user->Cartitems()->where('product_sku_id',$skuId)->first())
		{
			//如果已经存在，将数量加上
			$cart->update(['amount'=>$cart->amount + $amount]);

		}else{
			//否则创建一个新的订单记录
			$item = new CartItem(['amount'=>$amount]);
			$item->user()->associate($user);
			$item->productSku()->associate($skuId);
			$item->save();
		}

		return $item;
	}

	public function remove($skuIds)
	{
		//可以传打个ID 也可以多个ID
		if(!is_array($skuIds)){
			$skuIds = [$skuIds];
		}
		return Auth::user()->Cartitems()->whereIn('product_sku_id',$skuIds)->delete();
	}
} 
