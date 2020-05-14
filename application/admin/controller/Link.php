<?php
namespace app\admin\controller;
use think\Controller;

/**
 * Class linkController
 * @package app\admin\controller
 * 友情链接的操作
 */
class Link extends Controller
{

    //列表显示
    public function index(){
       $linkData = db('link')
           ->order('link_sort','desc')
           ->paginate(10);
       $page = $linkData->render();
       $this->assign([
           'linkData'=>$linkData,
           'page'=>$page
       ]);
       return view('index');
    }

    //增加
    public function add(){
        //判断是否时post请求
        if (request()->isPost()){
            $data = input('post.');
            //添加图片操作
            if($_FILES['logo']['tmp_name']){
                $data['logo'] = upload('logo');
            }

            //*****进行数据验证*******
//            $validate = validate('link');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //添加数据
            $re = db('link')->insert($data);
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
        $linkItem = db('link')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');

            //*****进行数据验证*******
//            $validate = validate('link');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //修改图片时，删除图片
            if ($_FILES['logo']['tmp_name']){
                //第一步：判断之前数据库是否上传过文件
                if ($linkItem['logo'] != ''){
                    //第二步：拼接真实的图片路径
                    $imgRealPath = UPLOADS_IMG.'/'.$linkItem['logo'];
                    if (file_exists($imgRealPath)){
                        @unlink($imgRealPath);
                    }
                }
                //获取路径填充
                $data['logo'] = upload('logo');
            }

            $re = db('link')->update($data);
            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }



        $this->assign([
            'linkItem'=>$linkItem
        ]);
        return view('edit');
    }

    //删除信息
    public function delete($id){
        $data = Array();
        //第一步：查询数据并显示出来
        $linkItem = db('link')
            ->where('id','=',$id)
            ->find();

        //第二步：删除旧图片
        if ($linkItem['logo'] != ''){
            //第二步：拼接真实的图片路径
            $imgRealPath = UPLOADS_IMG.'/'.$linkItem['logo'];
            if (file_exists($imgRealPath)){
                @unlink($imgRealPath);
            }
        }
        //第三步：删除数据
        $re = db('link')
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
                $re = db('link')->where('id',$key)->update(['link_sort'=>$v]);
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
