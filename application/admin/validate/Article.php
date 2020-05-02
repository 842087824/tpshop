<?php
namespace	app\admin\validate;
use	think\Validate;
/**
 * Created by PhpStorm.
 * User: PC-LW
 * Date: 2020-04-25
 * Time: 21:12
 */
class Article extends Validate{
    protected $rule = [
      'title'=>'require|unique:article', //判断为必填 | 唯一(表名)
      'cate_id'=>'require',
      'link_url'=>'url'
    ];

    protected $message = [
      'title.require'=>'标题名称必填',
      'title.unique'=>'标题名称不能重复',
      'cate_id.require'=>'所属栏目不能为空',
      'link_url.url'=>'链接地址不合理'
    ];
}
