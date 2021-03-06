<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//定义上传图片的全局变量
define("UPLOADS_IMG",ROOT_PATH . 'public' . DS . 'uploads');

/*
 * 上传图片公共函数
 */
function upload($fileRequestName){
    //	获取表单上传文件	例如上传了001.jpg
    $file	=	request()->file($fileRequestName);
    //	移动到框架应用根目录/public/uploads/	目录下
    if($file){
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            //	成功上传后	获取上传信息
            //	输出	jpg
//            echo $info->getExtension();
            //	输出	20160820/42a79759f284b767dfcb2a0197904287.jpg
            return	$info->getSaveName();
            //	输出	42a79759f284b767dfcb2a0197904287.jpg
            // echo	$info->getFilename();
            }else{
            //	上传失败获取错误信息
            echo	$file->getError();
        }
    }
}


/*
 * 字符串截取并且超出显示省略号
 */
function subtext($text, $length){
    if(mb_strlen($text, 'utf8') > $length){
        return mb_substr($text,0,$length,'utf8').'…';
    }
    return $text;
}

/**
 * 图片资源处理函数
 */
function scan_imgdir($dir=UEDITOR){
    $files = array();
    $dir_list = scandir($dir);//获取默认目录下面的文件
    foreach ($dir_list as $file){
        if ($file != '.' && $file != '..'){
            //判断是文件还是文件夹
            if (is_dir($dir.'/'.$file)){ //是文件夹
                $files[$file] = scan_imgdir($dir.'/'.$file);
            }else{ //是图片
                $files[] = $dir.'/'.$file;//图片的完整路径
            }
        }
    }
    return $files;
}