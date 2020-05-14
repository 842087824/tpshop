<?php
namespace app\admin\controller;
use catetree\Catetree;
use think\Controller;

/**
 * Class articleController
 * @package app\admin\controller
 * 文章的操作
 */
class article extends Controller
{

    //列表显示
    public function index(){

        $articleData = db('article')->select();
        //传递cate
        $cateData = db('cate')->select();
        $this->assign([
            'cateData'=>$cateData,
            'articleData'=>$articleData
        ]);

       return view('index');
    }

    //增加
    public function add(){
        //判断是否时post请求
        if (request()->isPost()){
            $data = input('post.');
            //添加图片操作
            if($_FILES['thumb']['tmp_name']){
                $data['thumb'] = upload('thumb');
            }
            //添加文章时间
            $data['addtime'] = time();

            //*****进行数据验证*******
            $validate = validate('Article');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            //添加数据
            $re = db('article')->insert($data);
            if ($re) {
                $this->success('操作成功', 'index');
            }else{
                $this->error('操作失败');
            }
        }

        //传递cate
        $cateData = db('cate')->select();
        $cateTree = new Catetree();
        $cateData = $cateTree->catetree($cateData);
        $this->assign([
            'cateData'=>$cateData
        ]);

        return view('add');
    }

    //修改
    public function edit($id){
        //查询数据并显示出来
        $articleItem = db('article')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');

            //*****进行数据验证*******
            $validate = validate('article');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            //修改图片时，删除图片
            if ($_FILES['thumb']['tmp_name']){
                //第一步：判断之前数据库是否上传过文件
                if ($articleItem['thumb'] != ''){
                    //第二步：拼接真实的图片路径
                    $imgRealPath = UPLOADS_IMG.'/'.$articleItem['thumb'];
                    if (file_exists($imgRealPath)){
                        @unlink($imgRealPath);
                    }
                }
                //获取路径填充
                $data['thumb'] = upload('thumb');
            }

            $re = db('article')->update($data);
            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }


        //传递cate
        $cateData = db('cate')->select();
        $cateTree = new Catetree();
        $cateData = $cateTree->catetree($cateData);
        $this->assign([
            'cateData'=>$cateData,
            'articleItem'=>$articleItem
        ]);

        return view('edit');
    }

    //删除信息
    public function delete($id){
        $data = Array();
        //第一步：查询数据并显示出来
        $articleItem = db('article')
            ->where('id','=',$id)
            ->find();

        //第二步：删除旧图片
        if ($articleItem['thumb'] != ''){
            //第二步：拼接真实的图片路径
            $imgRealPath = UPLOADS_IMG.'/'.$articleItem['thumb'];
            if (file_exists($imgRealPath)){
                @unlink($imgRealPath);
            }
        }
        //第三步：删除数据
        $re = db('article')
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
                $re = db('article')->where('id',$key)->update(['article_sort'=>$v]);
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

    //ueditor图片显示
    public function imagelist(){
        //读取public/uditor里面所有图片
        $files = scan_imgdir();
        //对二维数组进行拆分
        $imageList = array(); //组装成一个新的一位数组

        foreach ($files as $v){
            if (is_array($v)){ //是否是一个数组
                foreach ($v as $v1){ //对第二个一位数组进行数组解析
                    $v1 = str_replace(UEDITOR,HTTP_UEDITOR,$v1);
                    $imageList[] = $v1;
                }
            }else{ //非数组
                $v = str_replace(UEDITOR,HTTP_UEDITOR,$v);
                $imageList[] = $v;
            }
        }

        $this->assign([
            'imageList'=>$imageList
        ]);
        return view('imagelist');
    }

    //删除ueditor图片
    public function deleteUeditorImage($path){
        $data = ['status'=>0,'message'=>'删除失败'];
        $realpath = str_replace(HTTP_UEDITOR,UEDITOR,$path);
        if (file_exists($realpath)){
            @unlink($realpath);
            $data = ['status'=>1,'message'=>'删除成功'];
        }

        return $data;
    }
}
