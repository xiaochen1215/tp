<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Test extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //多条数据  二维数组结构
        //存到cookie 中 存储购物车数据 分析
        // $list = [
        //     '71_839' => ['id' => '71_839','goods_id' => '71','spec_goods_id' => '839','number' => 10,'is_selected' => 1],
        //     '71_840' => ['id' => '71_839','goods_id' => '71','spec_goods_id' => '840','number' => 20,'is_selected' => 1],
        // ];
        // cookie('cart',$list,7*86400);
        // //列表页取数据
        // $list = cookie('cart');

        // //加入购物车 添加一条购物车记录  71 840 30
        // $list = cookie('cart');
        // $key = '71_840';
        // if(isset($list[$key])){
        //     //存在相同记录 累加数量
        //     $list[$key]['number'] += 30;
        // }else{
        //     $list[$key] = ['goods_id' => '71','spec_goods_id' => '840','number' => 30];
        // }
        // cookie('cart',$list,7*86400);

        // //修改购买数量  71 840 40
        // $list = cookie('cart');
        // $key ='71_840';
        // $list[$key]['number'] = 40;
        // cookie('cart',$list,7*86400);
        // //修改选中状态  71  840  取消选中 is_selected 0
        // $list = cookie('cart');
        // $key = '71_840';
        // $list[$key]['is_selected'] = 0;
        // cookie('cart',$list,7*86400);
        // //删除一条记录
        // $list = cookie('cart');
        // $key = '71_840';
        // unset($list[$key]);
        // cookie('cart',$list,7*86400);

        //加入到购物车
        $row = ['goods_id' => 67, 'number' => 10, 'spec_goods_id' => 100,'is_seleted' => 1];
        //先从cookie中取所有数据  得到数组
        $data = cookie('cart') ?: [];
        $key = $row['goods_id'] . '_' . $row['spec_goods_id'];
        if(isset($data[$key])){
            //存在则累加购买数量
            $data[$key]['number'] += $row['number'];
        }else{
            //不存在则添加新纪录
            $data[$key] = $row;
        }
        //将数组重新保存到cookie
        cookie('cart',$data,86400*7);

        //修改购买数量
        $row = ['goods_id'=>67,'spec_goods_id'=>100,'number'=>30];
        $key = $row['goods_id'] . '_' . $row['spec_goods_id'];
        // $row = ['id'=>'67_100','number'=>30];
        // $key = $row['id'];
        $data = cookie('cart') ?: [];
        $data[$key]['number'] = $row['number'];
        cookie('cart',$data,86400);

        //删除
        $row = ['goods_id'=>67,'spec_goods_id'=>100,'number'=>30];
        $key = $row['goods_id'] . '_' . $row['spec_goods_id'];
        // $row = ['id'=>'67_100','number'=>30];
        // $key = $row['id'];
        $data = cookie('cart') ?: [];
        unset($data[$key]);
        cookie('cart',$data,86400*7);
 
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


    public function xxx(){
        // $es = \Elasticsearch\ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();
        // $params = [
        //     'index' => 'test_index',
        //     'type' => 'test_type',
        //     'id' => 100,
        //     'body' => ['id'=>100, 'title'=>'PHP从入门到精通', 'author' => '张三']
        // ];
        
        // $r = $es->index($params);
        // dump($r);die;
    }
    
}
