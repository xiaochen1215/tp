<?php

namespace app\common\model;

use think\Model;

class SpecValue extends Model
{
    function specBind(){
        return $this->belongsTo('Spec','spec_id','id')->bind('spec_name');
    }
}
