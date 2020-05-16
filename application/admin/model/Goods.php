<?php
/**
 * Created by PhpStorm.
 * User: PC-LW
 * Date: 2020-05-16
 * Time: 16:53
 */

namespace app\admin\model;
use think\Image;
use think\Model;

/**
 * Class Goods
 * @package app\admin\model
 * 商品模型文件
 */
class Goods extends Model{

    protected $field = true;

    //模型里面的 事件处理
    protected	static	function init(){
        /**
         * 解决上传图片 同时生成三张缩略图的问题
         */
        Goods::beforeInsert(function ($goods) {
            // 1. 判断是否有图片上传
            if ($_FILES['og_thumb']['tmp_name']){
                //第一步：上传原图
                $thumbName = $goods->uploadImg('og_thumb');
                //第二步：找到上传原图的真实路径 并生成三张缩略图的真实地址
                $ogThumb =  date("Ymd") . DS .$thumbName; //原图上传的具体路径
                $bigThumb = date("Ymd") . DS .'big_'.$thumbName; //大图路径
                $mdThumb =  date("Ymd") . DS .'md_'.$thumbName; //中图路径
                $smThumb =  date("Ymd") . DS .'sm_'.$thumbName; //小图路径

                //第三步：参照图像处理文档 --- 生成 3 张缩略图
                $image = Image::open(IMG_UPLOADS .$ogThumb);
                //	按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
                $image->thumb(500,	500)->save(IMG_UPLOADS .$bigThumb);
                $image->thumb(200,	200)->save(IMG_UPLOADS .$mdThumb);
                $image->thumb(80,	80)->save(IMG_UPLOADS .$smThumb);

                //第四步 添加数据到数据库
                $goods->og_thumb = $ogThumb;
                $goods->big_thumb = $bigThumb;
                $goods->md_thumb = $mdThumb;
                $goods->sm_thumb = $smThumb;
            }

            // 2. 设置商品编号
            $goods->goods_code = time().rand(1111111,999999);
        });

        /**
         * 1.解决添加会员价格时，插入对应的商品id
         * 2.解决商品相册上传时，插入对应的商品id和各自生成3张对应的缩略图
         * 后置事件
         */
        Goods::afterInsert(function ($goods){
            /**
             * 批量写入会员价格   解决添加会员价格时，插入对应的商品id
             */
            $mpriceArr = $goods->mp;
            $goodId = $goods->id;
            if ($mpriceArr){ //判断数组是否为空
                foreach ($mpriceArr as $k => $v){
                    if (trim($v) == ''){//没有对会员级别设置价格
                        continue;
                    }else{
                        db('member_price')->insert(['mlevel_id'=>$k,'mprice'=>$v,'goods_id'=>$goodId]);
                    }
                }
            }

            /**
             * 批量写入商品相册
             */
            if ($goods->_hasImgs($_FILES['goods_photo']['tmp_name'])){
                $files	=	request()->file('goods_photo');
                foreach($files	as	$file){
                    //	移动到框架应用根目录/public/uploads/	目录下
                    $info	=	$file->move(ROOT_PATH	.	'public'	.	DS	.	'uploads');
                    if($info){
                        //第一步：获取上传图片
                        $photoName = $info->getFilename();

                        //第二步：找到上传原图的真实路径 并生成三张缩略图的真实地址
                        $ogPhoto =  date("Ymd") . DS .$photoName; //原图上传的具体路径
                        $bigPhoto = date("Ymd") . DS .'big_'.$photoName; //大图路径
                        $mdPhoto =  date("Ymd") . DS .'md_'.$photoName; //中图路径
                        $smPhoto =  date("Ymd") . DS .'sm_'.$photoName; //小图路径

                        //第三步：参照图像处理文档 --- 生成 3 张缩略图
                        $image = Image::open(IMG_UPLOADS .$ogPhoto);
                        //	按照原图的比例生成一个最大为150*150的缩略图并保存为thumb.png
                        $image->thumb(500,	500)->save(IMG_UPLOADS .$bigPhoto);
                        $image->thumb(200,	200)->save(IMG_UPLOADS .$mdPhoto);
                        $image->thumb(80,	80)->save(IMG_UPLOADS .$smPhoto);

                        //第四步 删除原图
                        @unlink(IMG_UPLOADS.$ogPhoto);

                        //第五步 添加数据到数据库
                        db('goods_photo')
                            ->insert(['goods_id'=>$goodId,'big_photo'=>$bigPhoto,'md_photo'=>$mdPhoto,'sm_photo'=>$smPhoto]);
                    }else{
                        //	上传失败获取错误信息
                        echo	$file->getError();
                    }
                }


            }

        });
    }


    //判断商品相册是否有图片上传判断
    private function _hasImgs($temparr){
        foreach ($temparr as $k => $v){
            if ($v){
                return true;
            }
        }
        return false;
    }

    //上传图片并且生成 3 张缩略图
    function uploadImg($imgName){
        //	获取表单上传文件	例如上传了001.jpg
        $file	=	request()->file($imgName);
        //	移动到框架应用根目录/public/uploads/	目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' .DS . 'uploads');
            if($info){
                //	成功上传后	获取上传信息
                //	输出	jpg
//            echo $info->getExtension();
                //	输出	20160820/42a79759f284b767dfcb2a0197904287.jpg
//                return	$info->getSaveName();
                //	输出	42a79759f284b767dfcb2a0197904287.jpg
                 return	$info->getFilename();
            }else{
                //	上传失败获取错误信息
                echo $file->getError();
            }
        }
    }


}