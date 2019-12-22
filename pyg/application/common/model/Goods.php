<?php

namespace app\common\model;

use think\Model;

class Goods extends Model
{
    //定义商品goods - type关联关系 一个商品属于一个type模型
    public function typeBind(){
        // return返回 调用 belongsTo(属于) type表        字段绑定 数据表字段值
        return $this->belongsTo('Type','type_id','id')->bind('type_name');
    }
    //定义商品goods - type关联关系 一个商品属于一个type模型
    public function type(){
        // return返回 调用 belongsTo(属于) type表        字段绑定 数据表字段值
        return $this->belongsTo('Type','type_id','id');
    }

    //定义商品goods - 品牌brand关联关系 一个商品属于一个brand品牌
    public function brandBind(){
    // return返回 调用 belongsTo(属于) type表        字段绑定 数据表字段值
    return $this->belongsTo('Brand','brand_id','id')->bind(['brand_name'=>'name']);
    }
   //定义商品goods - 品牌brand关联关系 一个商品属于一个brand品牌
   public function brand(){
    // return返回 调用 belongsTo(属于) type表        字段绑定 数据表字段值
    return $this->belongsTo('Brand','brand_id','id');
    }

    //定义商品goods - 分类关联关系 一个商品属于一个category分类
    public function categoryBind(){
    // return返回 调用 belongsTo(属于) type表       字段绑定 数据表字段值
    return $this->belongsTo('Category','cate_id','id')->bind('cate_name');
    }
    //定义商品goods - 分类关联关系 一个商品属于一个category分类
    public function category(){
        // return返回 调用 belongsTo(属于) type表       字段绑定 数据表字段值
        return $this->belongsTo('Category','cate_id','id');
        }

    //定义商品-相册关联 一个商品有多个相册图片 hasMany(有多个的相册图片)  关联外键 goods_id 主键id
    public function goodsImages(){
        return $this->hasMany('GoodsImages','goods_id','id');
    }

    //定义商品SPU-规格商品SKU关联  一个商品SPU有多个SKU
    public function specGoods(){
        return $this->hasMany('SpecGoods','goods_id','id');
    }

    //设置获取器  对goods_attr字段转化为数组
    public function getGoodsAttrAttr($value){
        return $value ? json_decode($value, true) : [];
    }
}
