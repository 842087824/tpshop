<?php
namespace app\admin\controller;
use catetree\Catetree;
use think\Controller;

/**
 * Class goodsController
 * @package app\admin\controller
 * 商品的操作
 */
class Goods extends Controller
{

    //列表显示
    public function index(){
       $goodsData = db('goods')
           ->order('goods_sort','desc')
           ->paginate(10);
       $page = $goodsData->render();
       $this->assign([
           'goodsData'=>$goodsData,
           'page'=>$page
       ]);
       return view('index');
    }

    //增加
    public function add(){
        //判断是否时post请求
        if (request()->isPost()){
            $data = input('post.');

            //*****进行数据验证*******
            $validate = validate('goods');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            //模型添加数据
            $re = model('Goods')->save($data);

            if ($re) {
                $this->success('操作成功', 'index');
            }else{
                $this->error('操作失败');
            }
        }
        //1.会员级别
        $memberLevelData = db('member_level')->select();
        //2.获取商品类型
        $typeData = db('type')->field('id,type_name')->select();
        //3.获取商品所属栏目
        $categoryData  = db('category')->select();
        $cate = new Catetree();
        //3.1转化数据为无线级分类
        $categoryTree = $cate->catetree($categoryData);
        //3.2再次赋值给categoryData
        $categoryData = $categoryTree;
        //4.获取商品所属品牌
        $brandData = db('brand')->field('id,brand_name')->select();
        $this->assign([
            'memberLevelData'=>$memberLevelData,
            'typeData'=>$typeData,
            'categoryData'=>$categoryData,
            'brandData'=>$brandData
        ]);
        return view('add');
    }

    //修改
    public function edit($id){
        //查询数据并显示出来
        $goodsItem = db('goods')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');

            //*****进行数据验证*******
            $validate = validate('goods');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            //修改图片时，删除图片
            if ($_FILES['goods_img']['tmp_name']){
                //第一步：判断之前数据库是否上传过文件
                if ($goodsItem['goods_img'] != ''){
                    //第二步：拼接真实的图片路径
                    $imgRealPath = UPLOADS_IMG.'/'.$goodsItem['goods_img'];
                    if (file_exists($imgRealPath)){
                        @unlink($imgRealPath);
                    }
                }
                //获取路径填充
                $data['goods_img'] = upload('goods_img');
            }

            $re = db('goods')->update($data);
            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }



        $this->assign([
            'goodsItem'=>$goodsItem
        ]);
        return view('edit');
    }

    //删除信息
    public function delete($id){
        $data = Array();
        //第一步：查询数据并显示出来
        $goodsItem = db('goods')
            ->where('id','=',$id)
            ->find();

        //第二步：删除旧图片
        if ($goodsItem['goods_img'] != ''){
            //第二步：拼接真实的图片路径
            $imgRealPath = UPLOADS_IMG.'/'.$goodsItem['goods_img'];
            if (file_exists($imgRealPath)){
                @unlink($imgRealPath);
            }
        }
        //第三步：删除数据
        $re = db('goods')
            ->where('id','=',$id)
            ->delete();
        if ($re){
            $data = ['status'=>1,'message'=>'删除成功'];
        }else{
            $data = ['status'=>0,'message'=>'删除失败'];
        }

        ///第四步：传递数据给前台
        return $data;
    }


    //排序
    public function listOrder(){
        //标志位
        $flag = false;
        if (is_array($data = input('post.'))){
            foreach ($data as $key => $v){
                $re = db('goods')->where('id',$key)->update(['goods_sort'=>$v]);
                if ($re  != 0){
                    $flag = true;
                }
            }
        }

        //判断
        if ($flag){
            $this->success('排序成功','index');
        }else{
            $this->success('排序失败');
        }

    }
}
