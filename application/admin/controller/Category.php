<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree; //引入无限极分类
/**
 * Class BrandController
 * @package app\admin\controller
 * 商品栏目的操作
 */
class Category extends Controller
{

    //列表显示
    public function index(){
       $categoryData = db('category')
           ->order('cate_sort','desc')
           ->select();

        $cate = new Catetree();
       //排序处理
        if (request()->isPost()){
            $data = input('post.');
            //对子类和父类排序
            $cate->cateSort($data['sort'],db('category'));
            //判断给与提示
            $this->success('排序成功','index');
        }

       //转化数据为无线级分类
       $categoryTree = $cate->catetree($categoryData);

       //再次赋值给cateData
        $categoryData = $categoryTree;

       $this->assign([
           'categoryData'=>$categoryData
       ]);
       return view('index');
    }

    //增加
    public function add(){
        //判断是否时post请求
        if (request()->isPost()){
            $data = input('post.');

            //*****进行数据验证*******
//            $validate = validate('Cate');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //上传图片
            if ($_FILES['cate_img']['tmp_name']){
                $data['cate_img'] = upload('cate_img');
            }

            //添加数据
            $re = db('category')->insert($data);
            if ($re) {
                $this->success('操作成功', 'index');
            }else{
                $this->error('操作失败');
            }
        }

        $categoryList = db('category')->order('cate_sort','desc')->select();
        //进行排序
        $cate = new Catetree();
        $categoryList = $cate->catetree($categoryList);

        $this->assign([
           'categoryList'=>$categoryList
        ]);
        return view('add');
    }

    //修改
    public function edit($id){
        //查询数据并显示出来
        $categoryItem = db('category')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');

            //*****进行数据验证*******
//            $validate = validate('Cate');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //上传图片
            if ($_FILES['cate_img']['tmp_name']){
                //删除旧图 再 上传新图
                if ($categoryItem['cate_img'] != ''){
                    //第一步：拼接真实的图片路径
                    $imgRealPath = UPLOADS_IMG.'/'.$categoryItem['cate_img'];
                    if (file_exists($imgRealPath)){
                        @unlink($imgRealPath);
                    }
                }
                $data['cate_img'] = upload('cate_img');
            }

            $re = db('category')->update($data);
            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }

        $categoryList = db('category')->order('cate_sort','desc')->select();
        //进行排序
        $cate = new Catetree();
        $categoryList = $cate->catetree($categoryList);
        $this->assign([
            'categoryItem'=>$categoryItem,
            'categoryList'=>$categoryList
        ]);
        return view('edit');
    }

    //删除信息
    public function delete($id){

        $re = false;
        //第一步：创建当前数据表
        $cate = db('category');

        //第二步：获取当前id数据表的子栏目id
        $cateTree = new Catetree();
        $sonids = $cateTree->childrenids($id,$cate);
        $sonids[] = intval($id);//添加子元素id和父元素id

        //循环遍历所有id
        //删除子分类和当前分类所有的图片相关数据
        foreach ($sonids as $key => $categoryID){
            $categorySonsItem = db('category')->find($categoryID);
            //判断$categorySonsItem里面的cate_img是否为空
            if ($categorySonsItem['cate_img'] != ''){ //无图片信息
                $imgRealPath = UPLOADS_IMG.'/'.$categorySonsItem['cate_img'];
                if (file_exists($imgRealPath)){
                    @unlink($imgRealPath);
                }
            }
        }

        //第三步：删除数据
        $re = $cate->delete($sonids);
        if ($re){
            $data = ['status'=>1,'message'=>'删除成功'];
        }else{
            $data = ['status'=>0,'message'=>'删除失败'];
        }

        ///第四步：传递数据给前台
        return $data;
    }


}
