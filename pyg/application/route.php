<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

//定义后台接口模块的域名路由
Route::domain('adminapi.pyg.com',function(){
    //默认首页 adminapi.pyg.com 访问到 adminapi/index/index
    Route::get('/','adminapi/index/index');
    //以后再此配置adminapi模块的其他路由
    //比如 Route::resource('goods','adminapi/goods');

    //获取验证码地址的接口
    Route::get('verify','adminapi/login/verify');

    //显示验证码路由的图片
    \think\Route::get('captcha/[:id]', "\\think\\captcha\\CaptchaController@index");

    //登陆接口
    Route::post('login','adminapi/login/login');

    //退出接口
    Route::get('logout','adminapi/login/logout');

    //单图片上传
    Route::post('logo','adminapi/upload/logo');

    //多图片上传
    Route::post('images','adminapi/upload/images');

    //商品分类接口
    Route::resource('categorys','adminapi/category');

    //商品品牌接口
    Route::resource('brands','adminapi/brand',[],['id'=>'\d+']);

    //商品模型 (类型) 接口
    Route::resource('types','adminapi/type');

    //商品接口
    Route::resource('goods','adminapi/goods');

    //相册图片删除
    Route::delete('delpics/:id','adminapi/goods/delpics',[],['id'=>'\d+']);

});
