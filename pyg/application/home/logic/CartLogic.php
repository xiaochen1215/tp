<?php

namespace app\home\logic;

class CartLogic{

    //$goods_id 商品id
    //$spec_goods_id 规格商品SKU的id
    //$number 购买数量
    //加入购物车
    public static function addCart($goods_id,$spec_goods_id,$number,$is_selected=1){
        //判断登录状态  已登录 就添加到数据表 未登录就添加到cookie
        if(session('?user_info')){
            //已登录取出session会话保存的id  就添加到数据表
            $user_id = session('user_info.id');
            // dump($user_id);die;

            $where = [
                'user_id' => $user_id,
                'goods_id'=> $goods_id,
                'spec_goods_id'=>$spec_goods_id
            ];
            $info = \app\common\model\Cart::where($where)->find();
            if($info){
                //存在则累加数量
                $info->number += $number;   //商品的数量
                $info->is_selected = $is_selected;  //购物车的选中状态给它选中
                $info->save();   //新增保存到数据表
            }else{
                //不存在  添加新记录
                $where['number'] = $number;
                $where['is_selected'] = $is_selected;
                \app\common\model\Cart::create($where,true);   //就添加到cart数据表里
            }
        }else{
            //未登录 添加到cookie
            //去除已有的数据
            $data = cookie('cart') ?: [];
            //拼接当前数据的下标
            $key = $goods_id . '_' . $spec_goods_id;
            //判断是否存在相同记录
            if(isset($data[$key])){
                //存在则累加数量
                $data[$key]['number'] += $number;
                $data[$key]['is_selected'] = $is_selected;
            }else{
                //不存在则添加新纪录
                $data[$key] = [
                    'id' => $key,
                    'goods_id' => $goods_id,
                    'spec_goods_id' => $spec_goods_id,
                    'is_selected' => $is_selected,
                    'number' => $number
                ];
            }
            //重新保存数据到cookie
            cookie('cart',$data,7*86400);

        };
    }

    //查询所有购物记录
    public static function getAllCart(){
        //判断登录状态 已登录 查询数据表  未登录就取cookie
        if(session('?user_info')){
            //已登录 查询数据表
            $user_id = session('user_info.id');
            $data = \app\common\model\Cart::field('id,user_id,goods_id,spec_goods_id,number,is_selected')->where('user_id',$user_id)->select();
            //转化为标准的二维数组(统一格式)
            $data = (new \think\Collection($data))->toArray();
        }else{
            //未登录 取cookie ['10_20'=>[],'10_21'=>[],]
            $data = cookie('cart') ?: [];
            //转化为标准的二维数组 (统一格式)  去掉字符串下标
            $data = array_values($data);
        }
        return $data;
    }


    //登陆后将cookie购物车迁移到数据表
    public static function cookieToDb(){
        //从cookie中取出所有数据
        $data = cookie('cart') ?: [];
        //将数据添加/修改到数据表
        foreach($data as $v){
            self::addCart($v['goods_id'],$v['spec_goods_id'],$v['number']);
        }
        //删除cookie购物车数据
        cookie('cart',null);
    }

    public static function changeNum($id,$number){
        //判断登录状态  已登录修改数据表; 未登录修改cookie
        if(session('?user_info')){
            //已登录修改数据表
            $user_id = session('user_info.id');
            //只能修改当前用户自己的记录
            \app\common\model\Cart::update(['number' => $number],['id'=>$id,'user_id'=>$user_id]);
            // \app\common\model\Cart::where(['id'=>$id,'user_id'=>$user_id])->update(['number' => $number]);

        }else{
            //未登录修改cookie
            //先从cookie取出所有记录
            $data = cookie('cart') ?: [];
            //修改数量
            $data[$id]['number'] = $number;
            //重新保存到cookie
            cookie('cart',$data,86400*7);
        }
    }

    
    public static function delCart($id){
        //判断登录状态 已登录就删除数据表   未登录就删除cookie
        if(session('?user_info')){
            //已登录 删除数据表
            $user_id = session('user_info.id');
            //Cart::destroy(['id'=>$id,'user_id' => $user_id]);
            \app\common\model\Cart::where(['id' => $id, 'user_id' => $user_id])->delete();
        }else{
            //未登录删除cookie
            //从cookie中取出所有
            $data = cookie('cart') ?: [];
            //$id 就是一个下标
            unset($data[$id]);
            //重新保存到cookie
            cookie('cart',$data,86400*7);
        }
    }

    //修改选中状态  $id 修改条件  $is_selected 状态 1 0
    //修改选中状态
    public static function changeStatus($id, $is_selected)
    {
        //判断登录状态
        if(session('?user_info')){
            //登录，修改数据表
            $user_id = session('user_info.id');
            $where['user_id'] = $user_id;
            if($id != 'all'){
                //修改一条
                $where['id'] = $id;
            }
            //修改
            \app\common\model\Cart::update(['is_selected'=>$is_selected], $where, true);
        }else{
            //修改cookie
            $data = cookie('cart') ?: [];
            if($id != 'all'){
                //修改一条
                $data[$id]['is_selected'] = $is_selected;
            }else{
                //修改所有
                foreach($data as $k=>$v){
                    $data[$k]['is_selected'] = $is_selected;
                }
            }
            //重新保存
            cookie('cart', $data, 86400*7);
        }
    }
}