<?php
namespace app\admin\controller;
use think\Controller;
use catetree\Catetree; //引入无限极分类
/**
 * Class BrandController
 * @package app\admin\controller
 * 文章 栏目的操作
 */
class Cate extends Controller
{

    //列表显示
    public function index(){
       $cateData = db('cate')
           ->order('cate_sort','desc')
           ->select();

        $cate = new Catetree();
       //排序处理
        if (request()->isPost()){
            $data = input('post.');
            //对子类和父类排序
            $cate->cateSort($data['sort'],db('cate'));
            //判断给与提示
            $this->success('排序成功','index');

        }

       //转化数据为无线级分类
       $cateTree = $cate->catetree($cateData);

       //再次赋值给cateData
       $cateData = $cateTree;

       $this->assign([
           'cateData'=>$cateData
       ]);
       return view('index');
    }

    //增加
    public function add(){
        //判断是否时post请求
        if (request()->isPost()){
            $data = input('post.');

            //判断是否可以添加子栏目
            //1.系统不让添加子栏目 id = 1
            //2.网店信息不让添加子栏目 id = 3
            if (in_array($data['pid'],['1','3'])){
                $this->error('系统分类不可以作为上级栏目');
            }

            //对于帮助分类下面的cate_type设置为3
            if ($data['pid'] == 2){
                $data['cate_type'] = 3;  //设置为网店帮助类型
            }

            //获取上一级id ，我们这里判断的是 id != 2 的网店帮助才可以添加
            $cateId = db('cate')
                ->field('pid')
                ->find($data['pid']);
            $cateId = $cateId['pid'];
            if ($cateId == 2){ //代表当前是 上一级是网店帮助，所以不可以分类
                $this->error('此分类不可以作为上级分类');
            }

            //*****进行数据验证*******
            $validate = validate('Cate');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            //添加数据
            $re = db('cate')->insert($data);
            if ($re) {
                $this->success('操作成功', 'index');
            }else{
                $this->error('操作失败');
            }
        }

        $cateList = db('cate')->order('cate_sort','desc')->select();
        //进行排序
        $cate = new Catetree();
        $cateList = $cate->catetree($cateList);

        $this->assign([
           'cateList'=>$cateList
        ]);
        return view('add');
    }

    //修改
    public function edit($id){
        //查询数据并显示出来
        $cateItem = db('cate')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');

            //*****进行数据验证*******
            $validate = validate('Cate');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            //判断是否可以添加子栏目
            //1.系统不让添加子栏目 id = 1
            //2.网店信息不让添加子栏目 id = 3
            if (in_array($data['pid'],['1','3'])){
                $this->error('系统分类不可以作为上级栏目');
            }

            $re = db('cate')->update($data);
            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }


        $cateList = db('cate')->order('cate_sort','desc')->select();
        //进行排序
        $cate = new Catetree();
        $cateList = $cate->catetree($cateList);
        $this->assign([
            'cateItem'=>$cateItem,
            'cateList'=>$cateList
        ]);
        return view('edit');
    }

    //删除信息
    public function delete($id){

        //第一步：创建当前数据表
        $cate = db('cate');

        //第二步：获取当前id数据表的子栏目id
        $cateTree = new Catetree();
        $sonids = $cateTree->childrenids($id,$cate);
        $sonids[] = intval($id);//添加子元素id和父元素id
//        var_dump($sonids);die;

        //不让删除内置分类
        $arr1 = [1,2,3];  //内置分类id 不让删除
        if (array_intersect($arr1,$sonids)){ //求两个数组的交集
            $this->error('系统内置分类不允许删除');
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
