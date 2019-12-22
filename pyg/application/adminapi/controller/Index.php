<?php
namespace app\adminapi\controller;


class Index extends BaseApi
{
    public function index()
    {
        //一对一关联
        //查询管理员信息 以及 档案信息
        // $info = \app\common\model\Admin::find(1);
        // $this->ok($info->profile);

        //关联预载入
        // $info = \app\common\model\Admin::with('profile')->find(1);
        // $info = \app\common\model\Admin::with('profile_bind')->find(1);
        // $this->ok($info);

        //查询档案信息 以及管理员信息
        // $info = \app\common\model\Profile::find();
        // $this->ok($info);
        // $this->ok($info->admin);
        // $info = \app\common\model\Profile::with('admin')->find(1);
        // $this->ok($info);

        //查询品牌信息以及分类
        // $info = \app\common\model\Brand::find(1);
        // $this->ok($info);
        // $this->ok($info->category);

        // $info = \app\common\model\Brand::with('category')->find(1);
        // $info = \app\common\model\Brand::with('category_bind')->find(1);
        // $this->ok($info);
        //查询分类 以及 分类下的品牌
        $info = \app\common\model\Category::with('brands')->find(72);
        $this->ok($info);

        // $user_id = \tools\jwt\Token::getUserId();
        // // $user_id = input('user_id');
        // $this->ok($user_id);
        //加密密码
        // $password = encrypt_password('123456');
        // $this->ok($password);


        // $token = \tools\jwt\Token::getToken(100);
        // $this->ok($token);

        // $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjNmMmc1N2E5MmFhIn0.eyJpc3MiOiJodHRwOlwvXC9hZG1pbmFwaS5weWcuY29tIiwiYXVkIjoiaHR0cDpcL1wvd3d3LnB5Zy5jb20iLCJqdGkiOiIzZjJnNTdhOTJhYSIsImlhdCI6MTU3NTE4NzA1NywibmJmIjoxNTc1MTg3MDU2LCJleHAiOjE1NzUyNzM0NTcsInVzZXJfaWQiOjEwMH0.BDsSMtB1khfhvYK23cQ_0cN9GFITWTnY74l0JJlOVf8";

        // $user_id = \tools\jwt\Token::getUserId($token);
        // $this->ok($user_id);

        //从请求头获取token
        // $token = \tools\jwt\Token::getRequestToken();
        // $this->ok($token);

        // return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="http://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="http://ad.topthink.com/Public/static/client.js"></script><thinkad id="ad_bd568ce7058a1091"></thinkad>';
        // $goods = \think\Db::table('pyg_goods')->find();
        // dump($goods);
        //返回数据
        //返回 code 200 msg success  data[]
        // $this->response();

        // $this->ok();
        // //返回 具体的数据
        // $data = \think\Db::table('pyg_goods')->select();
        // $this->response(200,'success',$data);
        // $this->ok($data);
        // //返回 失败提示
        // // $this->respinse(400,'参数错误');
        // $this->fail('参数错误');
        // $this->fail('参数错误',400);
    }
}
