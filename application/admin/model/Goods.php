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
         * 3.解决商品属性，插入对应的商品id 和 添加进入对应的数据库表
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


                        //第五步 添加数据到数据库
                        db('goods_photo')
                            ->insert(['goods_id'=>$goodId,'og_photo'=>$ogPhoto,'big_photo'=>$bigPhoto,'md_photo'=>$mdPhoto,'sm_photo'=>$smPhoto]);
                    }else{
                        //	上传失败获取错误信息
                        echo	$file->getError();
                    }
                }
            }

            /**
             * 处理商品属性
             */
            $goodsData = input('post.');
            $i = 0;
            if (isset($goodsData['goods_attr'])){
                foreach ($goodsData['goods_attr'] as $k => $v){
                    if (is_array($v)){//判断是否为数组
                        if (!empty($v)){ //循环goods_attr数组
                            foreach ($v as $k1 => $v1){
                                if (!$v1){
                                    $i++;
                                    continue;
                                }
                                db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v1,'attr_price'=>$goodsData['attr_price'][$i],'goods_id'=>$goodId]);
                                $i++;
                            }
                        }
                    }else{ //判断为字符串 也就是唯一属性 不涉及价格问题
                        db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v,'goods_id'=>$goodId]);
                    }
                }
            }
        });


        /**
         * 删除商品用，前置删除
         * 1.删除关联的三张表数据 和 图片(商品会员表、商品属性表、商品相册表)
         * 2.删除本身的数据 和 图片
         * 3.删除第四张表 商品库存表
         */
        Goods::beforeDelete(function($goods){
            $goodsId = $goods->id;
            //1.删除主图 及其 缩略图
            if ($goods->og_thumb){ //判断是否有图片
                $thumb = [];
                $thumb[] = IMG_UPLOADS.$goods->og_thumb;//原图
                $thumb[] = IMG_UPLOADS.$goods->big_thumb;//大图
                $thumb[] = IMG_UPLOADS.$goods->md_thumb;//中图
                $thumb[] = IMG_UPLOADS.$goods->sm_thumb;//小图

                foreach ($thumb as $k => $v){
                    if (file_exists($v)){
                        @unlink($v);
                    }
                }
            }
            //2.删除会员价格
            db('member_price')->where('goods_id','=',$goodsId)->delete();
            //3.删除关联的商品属性
            db('goods_attr')->where('goods_id','=',$goodsId)->delete();
            //4.删除库存表
            db('product')->where('goods_id','=',$goodsId)->delete();
            //5.删除商品相册及其缩略图
            $goodsPhotoRes  = model('GoodsPhoto')->where('goods_id','=',$goodsId)->select();
            if (!empty($goodsPhotoRes)){
                foreach ($goodsPhotoRes as $k => $v){
                    if ($v->og_photo){ //判断是否有图片
                        $photo = [];
                        $photo[] = IMG_UPLOADS.$v->og_photo;//原图
                        $photo[] = IMG_UPLOADS.$v->big_photo;//大图
                        $photo[] = IMG_UPLOADS.$v->md_photo;//中图
                        $photo[] = IMG_UPLOADS.$v->sm_photo;//小图

                        foreach ($photo as $k1 => $v1){
                            if (file_exists($v1)){
                                @unlink($v1);
                            }
                        }
                    }
                }
            }
            model('GoodsPhoto')->where('goods_id','=',$goodsId)->delete();
        });


        /**
         * 修改商品
         * 1.如果上传了商品主图,删除商品主图 和 3 张缩略图，再重新上传新的商品主图
         */
        Goods::beforeUpdate(function ($goods){
            $goodsId = $goods->id;

            /**
             * 二、新增商品属性
             */
            $goodsData = input('post.');
            if (isset($goodsData['goods_attr'])){
                $i = 0;
                foreach ($goodsData['goods_attr'] as $k => $v){
                    if (is_array($v)){//判断是否为数组
                        if (!empty($v)){ //循环goods_attr数组
                            foreach ($v as $k1 => $v1){
                                if (!$v1){
                                    $i++;
                                    continue;
                                }
                                db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v1,'attr_price'=>$goodsData['attr_price'][$i],'goods_id'=>$goodsId]);
                                $i++;
                            }
                        }
                    }else{ //判断为字符串 也就是唯一属性 不涉及价格问题
                        db('goods_attr')->insert(['attr_id'=>$k,'attr_value'=>$v,'goods_id'=>$goodsId]);
                    }
                }
            }
            //三、修改商品属性
            if (isset($goodsData['old_goods_attr'])){
                $attrPrice = $goodsData['old_attr_price'];
                $idsArr = array_keys($attrPrice);//获取数组所有key值
                $valuesArr = array_values($attrPrice);//获取数组所有value值
                $i = 0;
                foreach ($goodsData['old_goods_attr'] as $k => $v){
                    if (is_array($v)){//判断是否为数组
                        if (!empty($v)){ //循环old_goods_attr数组
                            foreach ($v as $k1 => $v1){
                                if (!$v1){
                                    $i++;
                                    continue;
                                }
                                db('goods_attr')->update(['attr_value'=>$v1,'attr_price'=>$valuesArr[$i],'id'=>$idsArr[$i]]);
                                $i++;
                            }
                        }
                    }else{ //判断为字符串 也就是唯一属性 不涉及价格问题
                        db('goods_attr')->update(['attr_value'=>$v,'attr_price'=>$valuesArr[$i],'id'=>$idsArr[$i]]);
                        $i++;//因为有隐藏表单了
                    }
                }
            }

            //四、商品相册处理



            // 1. 判断是否有图片上传
            if ($_FILES['og_thumb']['tmp_name']){
                //如果存在之前上传的图片，就删除旧的缩略图 old_og_thumb前台页面隐藏上传
                if ($goods->old_og_thumb){
                    @unlink(IMG_UPLOADS.$goods->old_og_thumb);
                    @unlink(IMG_UPLOADS.$goods->old_big_thumb);
                    @unlink(IMG_UPLOADS.$goods->old_md_thumb);
                    @unlink(IMG_UPLOADS.$goods->old_sm_thumb);
                }

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
            //2.处理会员  先删除全部数据 新的插入数据
            db('member_price')->where('goods_id','=',$goodsId)->delete();
            /**
             * 批量写入会员价格
             * 解决添加会员价格时，插入对应的商品id
             */
            $mpriceArr = $goods->mp;
            if ($mpriceArr){ //判断数组是否为空
                foreach ($mpriceArr as $k => $v){
                    if (trim($v) == ''){//没有对会员级别设置价格
                        continue;
                    }else{
                        db('member_price')->insert(['mlevel_id'=>$k,'mprice'=>$v,'goods_id'=>$goodsId]);
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