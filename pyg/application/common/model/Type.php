<?php

namespace app\common\model;

use think\Model;

class Type extends Model
{ 
    //定义 type 模型 和 规格名称spec的关联关系 一个模型下有多个spec
    public function specs(){
        return $this->hasMany('spec','type_id','id');
    }

    //定义 type模型 和 属性attribute的关联 一个type有多个属性attribute
    public function attrs(){
        return $this->hasMany('Attribute','type_id','id');
    }
}
