<?php

namespace app\common\model;

use think\Model;

class Admin extends Model
{
    //定义关联关系 一个管理员有一份档案 id uid id是管理员表主键
    public function profile(){

        //参数: 模型名 关联外键  (默认取admin_id) 关联主键(默认ID)
        return $this->hasOne('Profile','uid','id');
    }

    public function profileBind(){

        //参数: 模型名 关联外键  (默认取admin_id) 关联主键(默认ID)
        //绑定指定字段属性到父模型
        return $this->hasOne('Profile','uid','id')->bind('idnum,card');
    }
}
