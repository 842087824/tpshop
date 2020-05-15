<?php
namespace	app\admin\validate;
use	think\Validate;
/**
 * Created by PhpStorm.
 * User: PC-LW
 * Date: 2020-04-25
 * Time: 21:12
 */
class Conf extends Validate{
    protected $rule = [
      'ename'=>'require|unique:conf', //判断为必填 | 唯一(表名)
      'cname'=>'require|unique:conf'
    ];

    protected $message = [
      'ename.require'=>'英文名称不能为空',
      'ename.unique'=>'英文名称不能重复',
      'cname.require'=>'中文名称不能为空',
      'cname.unique'=>'中文名称不能重复'
    ];
}
