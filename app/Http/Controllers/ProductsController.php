<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Exceptions\InvalidRequestException;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
    	//创建一个查询构造器
    	$builder = Product::query()->where('on_sale',true);
    	//判断提交的参数是否有search参数，如果有就赋值给$search
    	//search 是用来模糊搜索的
    	if($search = $request->input('search','')){
    		$like = '%'.$search.'%';
    		//模糊搜索商品标题，商品详情，SKU标题 SKU描述
    		$builder->where(function($query) use($like){
    			$query->where('title','like',$like)
    				  ->orWhere('description','like',$like)
    				  ->orWhereHas('skus',function($query) use($like){
    				  	 $query->where('title','like',$like)
    				  	 	   ->where('description','like',$like);
    				  });
    		});
    	}

    	//判断是否有提交order参数 如果有就赋值给$order
    	//order 是用来控制商品的排序规则的
    	if($order = $request->input('order','')){
    		//是否已_asc 或_desc 结尾
    		if(preg_match('/^(.+)_(asc|desc)$/',$order,$m)){
    			//如果字符串的串开头是这三个字符串之一，说明是一个合法的排序值
    			if(in_array($m[1], ['price','sold_count','rating'])){
    				//根据传入的排序值构造排序参数
    				$builder->orderBy($m[1],$m[2]);
    			}
    		}
    	}

    	$products = $builder->paginate(16);

    	return view('products.index',[
    		'products'=>$products,
    		'filters'=>[
    			'search'=>$search,
    			'order'=>$order
    		],
    	]);
    }

    public function show(Request $request,Product $product)
    {
    	//判断商品是否已经上架，如果没有上架着抛出异常
    	if(!$product->on_sale){
    		throw new InvalidRequestException('商品未上架');
    	}

    	$favored = false;
    	//用户为登陆时返回的是null 登录返回的是对应的用户对象
    	if($user = $request->user()){
    		 // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品
            // boolval() 函数用于把值转为布尔值
    		$favored = boolval($user->favoriteProducts()->find($product->id));
    	}

    	return view('products.show',['product'=>$product,'favored'=>$favored]);
    }

    //收藏商品
    public function favor(Product $product,Request $request)
    {
    	$user = $request->user();

    	//判断该用户是否已经收藏了改商品
    	if($user->favoriteProducts()->find($product->id)){
    		return [];
    	}

    	$user->favoriteProducts()->attach($product);
    	return [];
    }

    //取消收藏
    public function disfavor(Product $product,Request $request)
    {
    	$user = $request->user();
    	$user->favoriteProducts()->detach($product);
    	return [];
    }

    public function favorites(Request $request)
    {
    	$products = $request->user()->favoriteProducts()->paginate(16);

    	return view('products.favorites',['products'=>$products]);
    }
}
