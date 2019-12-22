<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Cart extends Base
{
    //加入购物车
    public function addcart(){
        //如果是get请求，直接跳转到首页
        if(request()->isGet()){
            $this->redirect('home/index/index');
        }
        //post请求  表单处理
        $params = input();
        $validate = $this->validate($params, [
            'goods_id' => 'require',
            //'spec_goods_id' => 'require',
            'number' => 'require'
        ]);
        if($validate !== true){
            $this->error($validate);
        }
        //数据处理 调用封装的方法
        \app\home\logic\CartLogic::addCart($params['goods_id'], $params['spec_goods_id'], $params['number']);
        //调用封装的静态方法取出数据展示 加入成功的页面
        //查询商品相关信息
        $goods = \app\home\logic\GoodsLogic::getGoodsWithSpecGoods($params['goods_id'], $params['spec_goods_id']);
        return view('addcart', ['goods'=>$goods, 'number'=>$params['number']]);

    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //查询所有的购物记录
        $list = \app\home\logic\CartLogic::getAllCart();
        //对每一条购物记录 查询商品相关信息(商品信息和SKU信息)
        foreach($list as $k=>$v){
            // $v['goods_id']  $v['spec_goods_id']
            $list[$k]['goods'] = \app\home\logic\GoodsLogic::getGoodsWithSpecGoods($v['goods_id'], $v['spec_goods_id']);
            //直接使用$v 修改$list中的数据，foreach中的$v需要加&引用符号
            //$v['goods'] = \app\home\logic\GoodsLogic::getGoodsWithSpecGoods($v['goods_id'], $v['spec_goods_id']);//
        }
        unset($v);
        //购物车列表
        return view('index',['list' => $list]);
    }


    //修改Cart控制器，增加一个changenum方法，用于ajax请求。
    public function changenum(){
        //接收参数  id number 
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'id' => 'require',
            'number' => 'require|integer|gt:0'
        ]);
        if($validate !== true){
            $res = ['code' => 400, 'msg' => '参数错误'];
            echo json_encode($res);die;
        }
        //处理数据
        \app\home\logic\CartLogic::changeNum($params['id'],$params['number']);
        //返回数据
        $res = ['code' => 200, 'msg' => 'success'];
        return json($res);
    }

    public function delcart(){
        //接收参数
        $params = input();
        //参数检查 略
        if(!isset($params['id']) || empty($params['id'])){
            $res = ['code' => 400, 'msg' => '参数错误'];
            echo json_encode($res);die;
        }
        //处理数据
        \app\home\logic\CartLogic::delCart($params['id']);
        //返回数据
        $res = ['code' => 200, 'msg' => 'success'];
        echo json_encode($res);die;
    }

    public function changestatus(){
        //接收参数
        $params = input();
        // dump($params);die;
        //参数检测
        $validate = $this->validate($params,[
            'id' => 'require',
            'status' => 'require|in:0,1'
        ]);
        if($validate !== true){
            $res = ['code' => 400,'msg' => $validate];
            echo json_encode($res);die;
        }
        //处理数据
        \app\home\logic\CarLogic::changeStatus($params['id'],$params['status']);
        //返回数据
        $res = ['code' => 200, 'msg' => 'success'];
        echo json_encode($res);die;
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
