<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CouponCode extends Model
{
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENT = 'percent';

    public static $typeMap = [
    	self::TYPE_FIXED => '固定金额',
    	self::TYPE_PERCENT => '比例'
    ];

    protected $fillable = [
    	'name',
    	'code',
    	'type',
    	'value',
    	'total',
    	'used',
    	'min_amount',
    	'not_before',
    	'not_after',
    	'enabled',
    ];

    protected $casts = [

    	'enabled' => 'boolean',
    ];

    protected $dates = ['not_before','not_after'];
    protected $appends = ['description'];

    public static function findAvailableCode($length = 16)
    {
    	do {
    		//随机生成一个指定长度的字符串，转成大写
    		$code = strtoupper(Str::random($length));
    		//如果码已经存在则循环生成
    	}while(self::query()->where('value',$code)->exists());

    	return $code;
    }

    public function getDescriptionAttribute()
    {
    	$str = '';
    	if ($this->min_amount > 0){
    		$str = '满'.str_replace('.00','',$this->min_amount);
    	}

    	if ($this->type === self::TYPE_PERCENT){
    		return $str.'优惠'.str_replace('.00','',$this->value).'%';
    	}

    	return $str.'减'.str_replace('.00','',$this->value);
    }
}
