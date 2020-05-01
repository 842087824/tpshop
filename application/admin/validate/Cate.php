<?php
namespace	app\admin\validate;
use	think\Validate;
/**
 * Created by PhpStorm.
 * User: PC-LW
 * Date: 2020-04-25
 * Time: 21:12
 */
class Cate extends Validate{
    protected $rule = [
      'cate_name'=>'require|unique:cate'//判断为必填 | 唯一(表名)
    ];

    protected $message = [
      'cate_name.require'=>'分类名称必填',
      'cate_name.unique'=>'分类名称不能重复'
    ];
}
