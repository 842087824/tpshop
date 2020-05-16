<?php
namespace app\admin\controller;
use think\Controller;

/**
 * Class member_levelController
 * @package app\admin\controller
 * 会员级别的操作
 */
class MemberLevel extends Controller
{

    //列表显示
    public function index(){
       $memberLevelData = db('member_level')->paginate(10);
       $page = $memberLevelData->render();
       $this->assign([
           'memberLevelData'=>$memberLevelData,
           'page'=>$page
       ]);
       return view('index');
    }

    //增加
    public function add(){
        //判断是否时post请求
        if (request()->isPost()){
            $data = input('post.');

            if (intval($data['rate']) < 0 || intval($data['rate']) > 100){
                $this->error('折扣率数字区间为0~100');
            }
            //*****进行数据验证*******
//            $validate = validate('member_level');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //添加数据
            $re = db('member_level')->insert($data);
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
        $memberLevelItem = db('member_level')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');


            if (intval($data['rate']) < 0 || intval($data['rate']) > 100){
                $this->error('折扣率数字区间为0~100');
            }

            //*****进行数据验证*******
//            $validate = validate('member_level');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            $re = db('member_level')->update($data);
            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }

        $this->assign([
            'memberLevelItem'=>$memberLevelItem
        ]);
        return view('edit');
    }

    //删除信息
    public function delete($id){
        $re = db('member_level')
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

}
