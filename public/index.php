<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');
//定义UEDITOR上传的图片的位置
define('UEDITOR', __DIR__ . '/../public/ueditor');
define('HTTP_UEDITOR','/ueditor');
//定义上传图片的路径
define('IMG_UPLOADS', __DIR__ . '/../public/uploads/');

// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
