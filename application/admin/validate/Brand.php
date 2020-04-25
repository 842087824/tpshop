<?php
namespace	app\admin\validate;
use	think\Validate;
/**
 * Created by PhpStorm.
 * User: PC-LW
 * Date: 2020-04-25
 * Time: 21:12
 */
class Brand extends Validate{
    protected $rule = [
      'brand_name'=>'require|unique:brand', //判断为必填 | 唯一(表名)
      'brand_url'=>'require|url',
      'brand_desc'=>'min:6'
    ];

    protected $message = [
      'brand_name.require'=>'品牌名称必填',
      'brand_name.unique'=>'品牌名称不能重复',
      'brand_url.url'=>'品牌地址不正确',
      'brand_desc'=>'品牌描述最少6个字符'
    ];
}
