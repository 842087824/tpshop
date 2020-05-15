<?php
namespace app\admin\controller;
use think\Controller;

/**
 * Class attrController
 * @package app\admin\controller
 * 商品属性的操作
 */
class Attr extends Controller{

    //列表显示
    public function index(){
       $typeId = input('id');
       if($typeId){
           $map['type_id'] = ['=',$typeId];
       }else{
           $map = 1;
       }
       $attrData = db('attr')
           ->alias('a')
           ->field('a.*,t.type_name')
           ->join('type t','a.type_id = t.id')
           ->where($map)
           ->order('a.id','desc')
           ->paginate(10);
       $page = $attrData->render();
       $this->assign([
           'attrData'=>$attrData,
           'page'=>$page
       ]);
       return view('index');
    }

    //增加
    public function add(){
        //判断是否时post请求
        if (request()->isPost()){
            $data = input('post.');
            //中文,转化为英文,
            $data['attr_values'] = str_replace('，',',',$data['attr_values']);
            //*****进行数据验证*******
            $validate = validate('attr');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            //添加数据
            $re = db('attr')->insert($data);
            if ($re) {
                $this->success('操作成功', 'index');
            }else{
                $this->error('操作失败');
            }
        }

        $typeData = db('type')->select();
        $this->assign([
            'typeData'=>$typeData
        ]);
        return view('add');
    }

    //修改
    public function edit($id){
        //查询数据并显示出来
        $attrItem = db('attr')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');
            //中文,转化为英文,
            $data['attr_values'] = str_replace('，',',',$data['attr_values']);
            //*****进行数据验证*******
            $validate = validate('attr');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            $re = db('attr')->update($data);
            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }

        $typeData = db('type')->select();
        $this->assign([
            'attrItem'=>$attrItem,
            'typeData'=>$typeData
        ]);
        return view('edit');
    }

    //删除信息
    public function delete($id){
        $re = db('attr')
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
                $re = db('attr')->where('id',$key)->update(['attr_sort'=>$v]);
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
