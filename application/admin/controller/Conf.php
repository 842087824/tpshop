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
       $confData = db('conf')
           ->order('sort','desc')
           ->paginate(10);
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

            //如果是多选，替换中文”，“为英文","
            if ($data['form_type'] == 'radio' || $data['form_type'] == 'select' || $data['form_type'] == 'checkbox'){
                $data['values'] = str_replace('，',',',$data['values']);
                $data['value'] = str_replace('，',',',$data['value']);
            }

            //*****进行数据验证*******
            $validate = validate('Conf');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

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

            //如果是多选，替换中文”，“为英文","
            if ($data['form_type'] == 'radio' || $data['form_type'] == 'select' || $data['form_type'] == 'checkbox'){
                $data['values'] = str_replace('，',',',$data['values']);
                $data['value'] = str_replace('，',',',$data['value']);
            }

            //*****进行数据验证*******
            $validate = validate('Conf');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

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
                $re = db('conf')->where('id',$key)->update(['sort'=>$v]);
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

    //配置项
    public function conflist(){
        $conf = db('conf');

        if (request()->isPost()){
            $data = input('post.');
            // 复选框空勾选问题
            $checkFields2D = $conf->field('ename')->where(array('form_type'=>'checkbox'))->select();
            // 改为一维数组
            $checkFields = array();
            if($checkFields2D){
                foreach ($checkFields2D as $k => $v){
                    $checkFields[] = $v['ename'];
                }
            }
            //所有发送的字段组成的一个数组
            $allFields = array();
            // 处理文字数据
            foreach ($data as $k => $v){
                $allFields[] = $k;
                if(is_array($v)){
                    $value = implode(',',$v);
                    $conf->where(array('ename'=>$k))->update(['value'=>$value]);
                }else{
                    $conf->where(array('ename'=>$k))->update(['value'=>$v]);
                }
            }
            //如果复选框未选中任何一个选项，则设置为空
            foreach ($checkFields as $k => $v){
                if (!in_array($v,$allFields)){
                    $conf->where(array('ename'=>$v))->update(['value'=>'']);
                }
            }
            //处理图片数据
            if ($_FILES){
                foreach ($_FILES as $k => $v){
                    if ($v['tmp_name']){
                        //修改图片时，删除一个图片的原图片
                        $imgs = $conf->field('value')->where(array('ename'=>$k))->find();
                        if ($imgs){//判断value里面是否有值
                            $imgRealPath = UPLOADS_IMG.'/'.$imgs['value'];
                            if (file_exists($imgRealPath)){
                                @unlink($imgRealPath);
                            }
                        }
                        $imgPathSrc = upload($k);
                        $conf->where(array('ename'=>$k))->update(['value'=>$imgPathSrc]);
                    }
                }
            }
//            dump($_FILES);die;
            $this->success('配置成功');
        }

        $ShopConfRes = $conf->where(array('conf_type'=>1))->order('sort','desc')->select();
        $GoodsConfRes = $conf->where(array('conf_type'=>2))->order('sort','desc')->select();

        $this->assign([
            'ShopConfRes'=>$ShopConfRes,
            'GoodsConfRes'=>$GoodsConfRes
        ]);
        return view("conflist");
    }



}
