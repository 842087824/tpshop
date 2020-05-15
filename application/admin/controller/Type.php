<?php
namespace app\admin\controller;
use think\Controller;

/**
 * Class typeController
 * @package app\admin\controller
 * 商品分类的操作
 */
class Type extends Controller
{

    //列表显示
    public function index(){
       $typeData = db('type')->paginate(10);
       $page = $typeData->render();
       $this->assign([
           'typeData'=>$typeData,
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
//            $validate = validate('type');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //添加数据
            $re = db('type')->insert($data);
            if ($re) {
                $this->success('操作成功', 'index');
            }else{
                $this->error('操作失败');
            }
        }
        return view('add');
    }

    //修改
    public function edit($id){
        //查询数据并显示出来
        $typeItem = db('type')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');

            //*****进行数据验证*******
//            $validate = validate('type');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            $re = db('type')->update($data);
            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }

        $this->assign([
            'typeItem'=>$typeItem
        ]);
        return view('edit');
    }

    //删除信息
    public function delete($id){
        $re = db('type')
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
                $re = db('type')->where('id',$key)->update(['type_sort'=>$v]);
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
