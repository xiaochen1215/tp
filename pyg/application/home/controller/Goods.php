<?php

namespace app\home\controller;

use think\Controller;

class Goods extends Base
{
    //商品列表
    public function index($id){
        //查询分类下的商品列表  分页
        $list = \app\common\model\Goods::where('cate_id',$id)->order('id desc')->paginate(10);
        //查询分类名称
        $cate_info = \app\common\model\Category::find($id);
        $cate_name = $cate_info['cate_name'];
        return view('index', ['list' => $list,'cate_name' => $cate_name]);
    }

    //商品详情
    public function detail($id){

        //查询商品数据
        $goods = \app\common\model\Goods::with('goods_images,spec_goods')->find($id);
       
        if($goods['spec_goods']){
            $goods['goods_price'] = $goods['spec_goods'][0]['price'];
        }

 
        //查询数据 先得到以下结构的数据(二维数组)
        // $goods['spec_goods'] = [
        //     ['id'=> 836, 'goods_id'=>71, 'value_ids'=>'28_32'],
        //     ['id'=> 837, 'goods_id'=>71, 'value_ids'=>'28_33'],
        //     ['id'=> 838, 'goods_id'=>71, 'value_ids'=>'29_32'],
        //     ['id'=> 839, 'goods_id'=>71, 'value_ids'=>'29_33'],
        // ];
    
        //查询所有相关的规格名称规格
        $value_ids = array_column($goods['spec_goods'],'value_ids');   //['28_32','28_33','29_32','29_33',]
        
        $value_ids = implode('_',$value_ids);  //'28_32_28_33_29_32_29_33'
        
        $value_ids = explode('_',$value_ids);  //[28,32,28,33,29,32,29,33]
        $value_ids = array_unique($value_ids); //[28,29,32,33]
        
        $spec_values = \app\common\model\SpecValue::with('spec_bind')->where('id','in',$value_ids)->select();
        $spec_values = (new \think\Collection($spec_values))->toArray();
    
        // 得出 数组结构大致如下
        // $spec_values = [
        //     ['id'=> 28, 'spec_id'=>23, 'spec_value'=>'黑色', 'spec_name' => '颜色'],
        //     ['id'=> 29, 'spec_id'=>23, 'spec_value'=>'金色', 'spec_name' => '颜色'],
        //     ['id'=> 32, 'spec_id'=>24, 'spec_value'=>'128G', 'spec_name' => '内存'],
        //     ['id'=> 33, 'spec_id'=>24, 'spec_value'=>'金色', 'spec_name' => '内存'],
        // ];

        //转化为方便页面展示的数据结构
        $specs = [];
        foreach($spec_values as $v){
            //组装规格名称的数据
           
            $specs[$v['spec_id']] = [
                'id' => $v['spec_id'],
                'spec_name' => $v['spec_name'],
                'spec_values' => []
                
            ];
        }
        
        //得出以下数组
        // $specs = [
        //     '23' => ['id' =>23,'spec_name'=>'颜色','spec_values' => []],
        //     '24' => ['id' =>23,'spec_name'=>'颜色','spec_values' => []],
        // ];
        foreach($spec_values as $v){
            //组装规格值的数据
            $specs[$v['spec_id']]['spec_values'][] = $v;
        }

        // 经分析  方便页面遍历展示的结构如下 要做成下面这样
        // $specs = [
        //     '23' => ['id'=>23, 'spec_name' => '颜色', 'spec_values' => [
        //              ['id'=> 28, 'spec_id'=>23, 'spec_value'=>'黑色', 'spec_name' => '颜色'],
        //              ['id'=> 29, 'spec_id'=>23, 'spec_value'=>'金色', 'spec_name' => '颜色'],
        //          ]
        //      ],
        //     '24' => ['id'=>24, 'spec_name' => '内存', 'spec_values' => [
        //              ['id'=> 32, 'spec_id'=>24, 'spec_value'=>'128G', 'spec_name' => '内存'],
        //              ['id'=> 33, 'spec_id'=>24, 'spec_value'=>'金色', 'spec_name' => '内存'],
        //          ]
        //      ],
        // ];

        // 切换规格值改变价格 ，预期数组结构
        //$goods['spec_goods']
        // ['28_33' => ['id' => 827, 'price'=>'3500'], '28_32'=>['id' => 827, 'price'=>'3500']]
        $value_ids_map = [];
        // dump($goods['spec_goods']);die;
        foreach($goods['spec_goods'] as $v){
            $value_ids_map[$v['value_ids']] = [
                'id' => $v['id'],
                'price' => $v['price']
            ];
        }
        // dump($value_ids_map);die;
        //最终的目标结构
        /*$specs = [
            '100' => ['id'=>100, 'spec_name'=>'颜色', 'spec_values' => [
                ['id'=>200, 'spec_value'=>'黑色'],
                ['id'=>202, 'spec_value'=>'金色'],
            ]],
            '101' => ['id'=>101, 'spec_name'=>'内存', 'spec_values' => [
                ['id'=>203, 'spec_value'=>'128G'],
                ['id'=>204, 'spec_value'=>'256G'],
            ]],
        ];*/
        //$value_ids_map 是放到页面js中使用的  转化为json格式
        $value_ids_map = json_encode($value_ids_map);
        // dump($value_ids_map);die;


        return view('detail',['goods'=>$goods,'specs' => $specs,'value_ids_map' => $value_ids_map]);
    }
}
