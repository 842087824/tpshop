<?php
namespace app\admin\controller;
use think\Controller;

/**
 * Class confController
 * @package app\admin\controller
 * 配置管理的操作
 */
class Conf extends Controller
{

    //列表显示
    public function index(){
       $confData = db('conf')->paginate(10);
       $page = $confData->render();
       $this->assign([
           'confData'=>$confData,
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
//            $validate = validate('conf');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //添加数据
            $re = db('conf')->insert($data);
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
        $confItem = db('conf')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');

            //*****进行数据验证*******
//            $validate = validate('conf');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            $re = db('conf')->update($data);
            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }



        $this->assign([
            'confItem'=>$confItem
        ]);
        return view('edit');
    }

    //删除信息
    public function delete($id){
        $re = db('conf')
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
                $re = db('conf')->where('id',$key)->update(['conf_sort'=>$v]);
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
