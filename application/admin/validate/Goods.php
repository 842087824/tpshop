<?php
namespace	app\admin\validate;
use	think\Validate;
/**
 * Created by PhpStorm.
 * User: PC-LW
 * Date: 2020-04-25
 * Time: 21:12
 */
class Goods extends Validate{
    protected $rule = [
      'goods_name' => 'require|unique:goods',//判断为必填 | 唯一(表名)
      'category_id'=>'require',
      'market_price'=>'require',
      'shop_price'=>'require',
      'goods_weight'=>'require'
    ];

    protected $message = [
      'goods_name.require'=>'商品名称必填',
      'goods_name.unique'=>'商品名称不可以重复',
      'category_id.require'=>'商品所属栏目不可以选择为空',
      'market_price.require'=>'商品的市场价不可以为空',
//      'market_price.num'=>'商品的市场价必须为数字',
      'shop_price.require'=>'商品的市场价不可以为空',
//      'shop_price.num'=>'商品的市场价必须为数字',
      'goods_weight.require'=>'商品的重量不可以为空',
//      'goods_weight.num'=>'商品的重量必须为数字'
    ];
}
