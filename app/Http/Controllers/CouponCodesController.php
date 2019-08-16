<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\CouponCode;

class CouponCodesController extends Controller
{
    public function show($code)
    {
    	//判断优惠券是否存在
    	if (!$record = CouponCode::where('code',$code)->first()){
    		abort(404);
    	}

    	//如果优惠券没有启用，等同于不存在
    	if (!CouponCode::where($record->enabled)) {
    		abort(404);
    	}

    	if ($record->total - $record->used <=0 ) {
    		return response()->json(['该优惠券已经用完'],403);
    	}

    	if ($record->not_before && $record->not_before->gt(Carbon::now())) {
    		return response()->json(['msg'=>'该优惠券还不能使用'],403);
    	}

    	if ($record->not_after && $record->not_after->lt(Carbon::now())) {
    		return json(['msg'=>'该优惠券已经过期'],403);
    	}

    	return $record;
    }
}
