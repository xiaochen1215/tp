<?php

namespace app\home\logic;

class GoodsLogic{
    //根据商品id和sku的id 查询商品信息
    //$goods_id
    //$spec_goods_id
    public static function getGoodsWithSpecGoods($goods_id,$spec_goods_id=''){
        //如果有$spec_goods_id  就根据$spec_goods_id查询指定的SKU记录
        if($spec_goods_id){
            $where['t2.id'] = $spec_goods_id;
        }else{
            //如果没有$spec_goods_id  就根据$goods_id查询指定的商品记录
            $where['t1.id'] = $goods_id;
        }
        $goods = \app\common\model\Goods::alias('t1')
            ->join('pyg_spec_goods t2', 't1.id=t2.goods_id', 'left')
            ->field('t1.*, t2.id as spec_goods_id,t2.goods_id,t2.value_ids,t2.value_names,t2.price,t2.cost_price as cost_price2,t2.store_count,t2.store_frozen')
            ->where($where)
            ->find();
        if(!$goods) return [];
        //如果SKU记录有值 使用SKU的值覆盖商品表的值 (价格 库存)
        if($goods['price']>0){
            $goods['goods_price'] = $goods['price'];
        }
        if($goods['cost_price2']>0){
            $goods['cost_price'] = $goods['cost_price'];
        }
        if($goods['store_count']>0){
            $goods['goods_number'] = $goods['store_count'];
        }
        if($goods['store_frozen']>0){
            $goods['frozen_number'] = $goods['store_frozen'];
        }
        return $goods->toArray();
    }
}