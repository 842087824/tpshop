<?php
namespace	app\admin\validate;
use	think\Validate;
/**
 * Created by PhpStorm.
 * User: PC-LW
 * Date: 2020-04-25
 * Time: 21:12
 */
class Attr extends Validate{
    protected $rule = [
      'attr_name' => 'require'//判断为必填 | 唯一(表名)
    ];

    protected $message = [
      'attr_name.require'=>'商品属性名称必填'
    ];
}
