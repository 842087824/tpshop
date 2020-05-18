<?php
/**
 * Created by PhpStorm.
 * User: PC-LW
 * Date: 2020-05-18
 * Time: 22:17
 */

namespace app\admin\controller;
use think\Controller;

class GoodsAttr extends Controller{


    //ajax删除goods_attr信息
    public function ajaxDeleteGoodsAttr(){
        $id = input('id');
        $re = db('goods_attr')->where('id','=',$id)->delete();
        if ($re){
            $data = ['status'=>1,'message'=>'删除成功'];
        }else{
            $data = ['status'=>0,'message'=>'删除失败'];
        }

        //第四步：传递数据给前台
        return $data;
    }



}