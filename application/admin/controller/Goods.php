<?php
namespace app\admin\controller;
use catetree\Catetree;
use think\Controller;

/**
 * Class goodsController
 * @package app\admin\controller
 * 商品的操作
 */
class Goods extends Controller
{

    //列表显示
    public function index(){
       $goodsData = db('goods')
           ->alias('g')
           ->field('g.*,c.cate_name,t.type_name,b.brand_name,SUM(p.goods_number) gn')
           ->join('category c','g.category_id = c.id','LEFT')
           ->join('type t','g.type_id = t.id','LEFT')
           ->join('brand b','g.brand_id = b.id','LEFT')
           ->join('product p','g.id = p.goods_id','LEFT')
           ->group('g.id')
           ->order('goods_sort','desc')
           ->paginate(10);
       $page = $goodsData->render();
       $this->assign([
           'goodsData'=>$goodsData,
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
            $validate = validate('goods');
            if(!$validate->check($data)){
                $this->error($validate->getError());
            }

            //模型添加数据
            $re = model('Goods')->save($data);

            if ($re) {
                $this->success('操作成功', 'index');
            }else{
                $this->error('操作失败');
            }
        }
        //1.会员级别
        $memberLevelData = db('member_level')->select();
        //2.获取商品类型
        $typeData = db('type')->field('id,type_name')->select();
        //3.获取商品所属栏目
        $categoryData  = db('category')->select();
        $cate = new Catetree();
        //3.1转化数据为无线级分类
        $categoryTree = $cate->catetree($categoryData);
        //3.2再次赋值给categoryData
        $categoryData = $categoryTree;
        //4.获取商品所属品牌
        $brandData = db('brand')->field('id,brand_name')->select();
        $this->assign([
            'memberLevelData'=>$memberLevelData,
            'typeData'=>$typeData,
            'categoryData'=>$categoryData,
            'brandData'=>$brandData
        ]);
        return view('add');
    }

    //修改
    public function edit($id){
        //查询数据并显示出来
        $goodsItem = db('goods')
            ->where('id','=',$id)
            ->find();

        //修改数据
        if (request()->isPost()){
            $data = input('post.');

//            dump($data);die;
            //*****进行数据验证*******
//            $validate = validate('goods');
//            if(!$validate->check($data)){
//                $this->error($validate->getError());
//            }

            //模型数据修改商品
            $re = model('Goods')->update($data);

            if ($re){
                $this->success('修改成功','index');
            }else{
                $this->error('修改失败');
            }
        }

        //1.会员级别
        $memberLevelData = db('member_level')->select();
        //2.获取商品类型
        $typeData = db('type')->field('id,type_name')->select();
        //3.获取商品所属栏目
        $categoryData  = db('category')->select();
        $cate = new Catetree();
        //3.1 转化数据为无线级分类
        $categoryTree = $cate->catetree($categoryData);
        //3.2 再次赋值给categoryData
        $categoryData = $categoryTree;
        //4.获取商品所属品牌
        $brandData = db('brand')->field('id,brand_name')->select();
        //5.会员价格查询
        $_memberPriceData = db('member_price')->where('goods_id','=',$id)->select();
        //5.1 修改会员价格数据显示状态
        foreach ($_memberPriceData as $k => $v){
            $memberPriceData[$v['mlevel_id']] = $v;
        }
        //6.查询当前商品属性信息
        $attrData = db('attr')->where('type_id','=',$goodsItem['type_id'])->select();
        //7.查询当前商品拥有的商品属性
        $_goodsAttrData = db('goods_attr')->where('goods_id','=',$id)->select();
        foreach ($_goodsAttrData as $k => $v){
            $goodsAttrData[$v['attr_id']][] = $v;
        }
        //8.查询商品相册
        $goodsPhotoData = db('goods_photo')->where('goods_id','=',$id)->select();

//        dump($goodsPhotoData);die;
        $this->assign([
            'goodsItem'=>$goodsItem,
            'memberLevelData'=>$memberLevelData,
            'typeData'=>$typeData,
            'categoryData'=>$categoryData,
            'brandData'=>$brandData,
            'memberPriceData'=>$memberPriceData,
            'attrData'=>$attrData,
            'goodsAttrData'=>$goodsAttrData,
            'goodsPhotoData'=>$goodsPhotoData
        ]);
        return view('edit');
    }

    //删除信息
    public function delete($id){

        $re = model('goods')->destroy($id);

        if ($re){
            $data = ['status'=>1,'message'=>'删除成功'];
        }else{
            $data = ['status'=>0,'message'=>'删除失败'];
        }
        ///第四步：传递数据给前台
        return $data;
    }


    //商品库存
    public function product(){
        $id = input('id');

        if (request()->isPost()){
            //修改的时候 --- 删除原来所有的库存信息
            db('product')->where('goods_id','=',$id)->delete();

            $data = input('post.');
            $goodsAttr = $data['goods_attr'];
            $goodsNum = $data['goods_num'];
            $product = db('product');
            //循环goodsNum
            foreach ($goodsNum as $k => $v){
                $strArr = array();
                foreach ($goodsAttr as $k1 => $v1){
                    //判断单选属性 不可以为空
                    if (intval($v1[$k]) <= 0){
                        continue 2;
                    }
                    $strArr[] = $v1[$k];
                }
                sort($strArr);//排序
                $strArr = implode(',',$strArr);//把数组转换成一个字符串
                $product->insert([
                    'goods_id'=>$id,
                    'goods_number'=>$v,
                    'goods_attr'=>$strArr
                ]);
            }
            $this->success('操作成功','index');
        }


        //商品属性
        $_radioAttrData = db('goods_attr')
            ->alias('g')
            ->field('g.id,g.attr_id,g.attr_value,a.attr_name')
            ->join('attr a','g.attr_id = a.id')
            ->where(array('g.goods_id'=>$id,'a.attr_type'=>1))
            ->select();
        //获取商品库存信息
        $goodsProData = db('product')->where('goods_id','=',$id)->select();

        //改变数组结构
        $radioAttrData = array();
        foreach ($_radioAttrData as $k => $v){
            $radioAttrData[$v['attr_name']][]=$v;
        }

        $this->assign([
            'radioAttrData' => $radioAttrData,
            'goodsProData' => $goodsProData
        ]);
        return view('product');
    }

    //排序
    public function listOrder(){
        //标志位
        $flag = false;
        if (is_array($data = input('post.'))){
            foreach ($data as $key => $v){
                $re = db('goods')->where('id',$key)->update(['goods_sort'=>$v]);
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
