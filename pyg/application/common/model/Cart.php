<?php

namespace app\common\model;

use think\Model;

class Cart extends Model
{

    //定义购物车-商品关联  主键id在商品表 一个购物记录属于一个商品
    public function goods(){
        return $this->belongsTO('Goods','goods_id','id');
    }

    //定义购物车-SKU关联 主键id在SKU表 一个购物记录属于一个SKU
    public function specGoods(){
        return $this->belongsTo('SpecGoods','spec_goods_id','id');
    }
}
