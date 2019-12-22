<?php

namespace app\adminapi\controller;

use think\Controller;

class Upload extends BaseApi
{
    //单图片上传
    public function logo(){
        //接收参数
        $params = input();
        //参数检测
        if(!isset($params['type']) || empty($params['type'])){
            $this->fail('参数错误');
        }
        if(!in_array($params['type'],['goods','category','brand'])){
            $params['type'] = 'other';
        }
        //处理数据  (文件上传)
        $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . $params['type'];
        if(!is_dir($dir)) mkdir($dir);
        //上传文件
        $file = request()->file('logo');
        if(empty($file)){
            $this->fail('请上传文件');
        }
        //移动到指定目录下
        $info = $file->validate(['size'=>5*1024*1024,'type'=>'jpg,png,gif,jpeg','type'=>'image/jpeg','image/png','image/gif'])->move($dir);
        if(empty($info)){
            //上传失败
            $this->fail($file->getError());
        }
        //返回数据
        $logo = DS. 'uploads' . DS . $params['type'] . $info->getSaveName();
        $this->ok($logo);
    }

    // 多图片上传
    public function images(){
        //接收参数
        $type = input('type','goods');

        //参数检测
        if($type == 'goods'){
            $type = 'other';
        }
        //数据处理 文件上传
        $dir = ROOT_PATH . 'public' . DS . 'uploads' . DS . $type;
        if(!is_dir($dir)) mkdir($dir);
        $files = request()->file('images');
        if(empty($files) || !is_array($files)){
            $this->fail('请上传多个图片');
        }

        //初始化返回结果数组
        //定义结果数组
        $res = [
            'success' => [],
            'error' => []
        ];
        //循环遍历 上传文件
        foreach($files as $file){
           
            $info = $file->validate(['size'=>10*1024*1024,'type'=>'jpg,png,gif,jpeg','type'=>'image/jpeg','image/png','image/gif'])->move($dir);
            if($info){
                //上传成功
                $res['success'][] = DS . 'uploads' . DS . $type . DS . $info->getSaveName();
            }else{
                //上传失败
                 $res['error'][]=[
                     'name' => $file->getInfo('name'),
                     'msg' => $file->getError()
                 ];
            }


        }
        //返回数据
        $this->ok($res);
    }


}
