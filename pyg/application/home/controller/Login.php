<?php

namespace app\home\controller;

use think\Controller;

class Login extends Controller
{
    //登陆界面展示
    public function login(){
        //临时关闭模板布局
        $this->view->engine->layout(false);
        return view();
    }

    //注册页面展示
    public function register(){
        //临时关闭模板布局
        $this->view->engine->layout(false);
        return view();
    }

    //手机注册
    public function phone(){
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'phone|手机号' => 'require|regex:1[3-9]\d{9}|unique:user',
            'code|验证码' => 'require',
            'password|密码' => 'require|length:6,20',
            'repassword|确认密码' => 'require|length:6,20|confirm:password',
        ]);
        if($validate !== true){
            $this->error($validate);
        }
        //短信验证码校验
        $code = cache('register_code_' . $params['phone']);
        if($code != $params['code']){
            $this->error('验证码错误');
        }

        //验证码成功后失效 成功一次后失效
        cache('register_code_' . $params['phone'],null);
        cache('register_time_' . $params['phone'],null);

        //添加用户 添加数据到用户表
        //可选 让手机号作为用户名和昵称
        $params['username'] = $params['phone'];
        $params['nickname'] = encrypt_phone($params['phone']);

        //密码加密
        $params['password'] = encrypt_password($params['password']);
        //注册成功 手动登录
        //添加数据
        \app\common\model\User::create($params, true);
        //页面跳转 跳转到登录页
        $this->success('注册成功','home/login/login');

        //自动登录  设置登录标识到session 跳转到首页或者个人中心
        // $res = \app\common\model\User::create($params,true);
        // $res = \app\common\model\User::find($res['id']);
        // session('user_info',$res->toArray());
        // $this->success('注册成功','home/index/index');
    }

    //控制器sendcode方法，用于ajax请求发送短信验证码
    public function sendcode(){
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'phone|手机号' => 'require|regex:1[3-9]\d{9}'
        ]);
        if($validate !== true){
            return json(['code'=>400,'msg'=>$validate]);
            //echo json_encode(['code'=>400,'msg'=>$validate],JSON_UNESCAPED_UNICODE);
        }

        //同一个手机号 一分钟只能发一次 1312888888
        $last_time = cache('register_time_' . $params['phone']) ?: 0;
        if(time() - $last_time < 60){
            $res = [
                'code' => 500,
                'msg'  => '发送太频繁'
            ];
            echo json_encode($res);die;
        }

        //处理数据 发送短信
        // 生成4位随机数
        $code = mt_rand(1000,9999);
        $msg = '[性感小群]你正在博采网站进行手机验证,验证码是:'.$code.',3分钟有效!!';
        //$res = send_msg($params['phone'],$msg);
        $res = true;   //开发测试过程,假装短信发送成功
        //返回数据
        if($res === true){
            //发送成功
            //将验证码记录到缓存 用于后续校验
            //将验证码保存在缓存中
            cache('register_code_' . $params['phone'],$code,180);
            //return json(['code' => 200,'msg'=>'短信发送成功']);

            //记录发送时间  用于下次发送前 用做频率检测
            cache('register_time_' . $params['phone'],time(),180);

            return json(['code' => 200,'msg' => '短信发送成功','data'=>$code]); //开发测试过程
        }else{
            //发送失败
            return json(['code'=>401,'msg'=>$res]);
        }
    }

    // public function test(){
    //     $phone = '13128800210';
    //     $msg = '[性感小群]你正在博采网站进行手机验证,验证码是:'.$code.',3分钟有效!!';
    //     $res = send_msg($phone,$msg);
    //     dump($res);die;
    // }


    //登录表单提交
    public function dologin(){
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'username' => 'require',
            'password' => 'require|length:6,20',
        ]);
        if($validate !== true){
            $this->error($validate);
        }
        $password = encrypt_password($params['password']);
        //查询用户表 进行登录认证
        //手机号字段和邮箱字段 同时查询
        $info = \app\common\model\User::where('phone', $params['username'])->whereOr('email', $params['username'])->find();
        if($info && $info['password'] == $password){
            //设置登录标识 登陆成功
            session('user_info',$info->toArray());
            //直接跳转到 cookieTodb 进行购物车数据的迁移。
            \app\home\logic\CartLogic::cookieTodb();
            //页面跳转
            //从session取跳转地址
            $back_url = session('back_url') ?: 'home/index/index';
            $this->redirect($back_url);
           //关联第三方用户
           if(session('open_type') && session('open_id')){
            //添加信息到第三方用户表
            /*\app\common\model\OpenUser::create([
                'open_type' => session('open_type'),
                'openid' => session('open_id'),
                'user_id' => $user['id'],
            ]);*/
            $open_user = \app\common\model\OpenUser::where('open_type', session('open_type'))->where('openid', session('open_id'))->find();
            $open_user->user_id = $info['id'];
            $open_user->save();
        }
        if(session('open_nickname')){
            \app\common\model\User::update(['nickname'=>session('open_nickname')], ['id'=>$info['id']], true);
            //$user->nickname = session('open_nickname');
            //$user->save();
        }
            //页面跳转
            // var_dump($info);die;
            $this->redirect('home/index/index');
        }else{
            $this->error('用户名或密码错误');
        }
    }

    //退出 前台用户退出
    public function logout(){
        //清空session
        session(null);
        //页面跳转
        $this->redirect('home/login/login');
    }

    //qq登录回调地址
    public function qqcallback()
    {
        // echo 'xxx';die;
        //参考 plugins/qq/example/oauth/callback.php
        require_once("./plugins/qq/API/qqConnectAPI.php");
        $qc = new \QC();
        $access_token = $qc->qq_callback();
        $open_id = $qc->get_openid();
        //获取用户信息（昵称）
        $qc = new \QC($access_token, $open_id);
        //dump($qc);die;
        $info = $qc->get_user_info();
        // dump($info);die;
        //判断是否绑定过（是否第一次登录）
        //不关联用户
        // $user = \app\common\model\User::where('open_type', 'qq')->where('openid', $open_id)->find();
        // if(!$user){
        //     //没有绑定过 添加新记录
        //     \app\common\model\User::create([
        //         'open_type' => 'qq',
        //         'openid' => $open_id,
        //         'nickname' => $info['nickname']
        //     ], true);
        // }else{
        //     //绑定过  同步用户信息
        //     $user->nickname = $info['nickname'];
        //     $user->save();
        // }
        // $user = \app\common\model\User::where('open_type', 'qq')->where('openid', $open_id)->find();
        // //设置登录标识
        // session('user_info', $user->toArray());
        // //跳转到首页
        // $this->redirect('home/index/index');
        // 关联用户
        $open_user = \app\common\model\OpenUser::where('open_type', 'qq')->where('openid', $open_id)->find();
        if($open_user && !empty($open_user['user_id'])){
            //已经关联过  同步用户信息（昵称） 直接登录成功
            $user = \app\common\model\User::find($open_user['user_id']);
            $user->nickname = $info['nickname'];
            $user->save();
            //登录成功 设置登录标识
            session('user_info', $user->toArray());
            //迁移cookie购物车数据到数据表
            \app\home\logic\CartLogic::cookieTodb();
            //页面跳转
            //从session取跳转地址
            $back_url = session('back_url') ?: 'home/index/index';
            $this->redirect($back_url);
            //跳转到首页
            $this->redirect('home/index/index');
        }else{
            //给用户显示一个选择页面：没有账号则跳转注册页面；已有账号则跳转登录页面
            //添加记录到open_user表
            if(!$open_user){
                \app\common\model\OpenUser::create([
                    'open_type' => 'qq',
                    'openid' => $open_id
                ]);
            }
            //第三方账号信息放到session， 用于后续登录后关联用户
            session('open_type', 'qq');
            session('open_id', $open_id);
            session('open_nickname', $info['nickname']);
            //这里直接跳转到登录
            $this->redirect('home/login/login');
        }
        
    }

    //第三方支付宝跳转 回调地址
    public function alicallback(){
        // echo 'xxx';die;
        require_once('./plugins/alipay/oauth/service/AlipayOauthService.php');
        require_once('./plugins/alipay/oauth/config.php');
        $AlipayOauthService = new \AlipayOauthService($config);
        //获取auth_code
        $auth_code = $AlipayOauthService->auth_code();
        //获取access_token
        $access_token = $AlipayOauthService->get_token($auth_code);
        //获取用户信息 user_id  nick_name
        $info = $AlipayOauthService->get_user_info($access_token);
        // var_dump($info['nick_name']);die;
        $info = [
            'nick_name' => '',
            'user_id'   => $info['user_id']
        ];
        $openid = $info['user_id'];
        // var_dump($info);die;
        //接下来就是关联绑定用户的过程
        //判断是否已经关联绑定用户
        $open_user = \app\common\model\OpenUser::where('open_type','alipay')->where('openid',$openid)->find();
        if($open_user && $open_user['user_id']){
            //已经关联过用户  直接登陆成功
            //同步用户信息到用户表
            $user = \app\common\model\User::find($open_user['userid']);
            $user->nickname = $info['nick_name'];
            $user->save();
            //设置登录标识
            session('user_info',$user->toArray());
            //迁移cookie购物车数据到数据表
            \app\home\logic\CartLogic::cookieTodb();
            //从sessione中取跳转地址
            $back_url = session('back_url') ?: 'home/index/index';
            //跳转到指定地址
            $this->redirect($back_url);
            $this->redirect('home/index/index');
        }
        if(!$open_user){
            //第一次登录  没有记录 添加一条记录到open_user表
            $open_user = \app\common\model\OpenUser::create(['open_type' => 'alipay','openid'=>$openid]);
        }
        //让第三方账号去关联用户(可能是注册,也可能是登录)
        //记录第三方账号到session中  用于后续关联用户
        session('open_user_id',$open_user['id']);
        // echo 'xxx';die;
        session('open_nickname',$info['nick_name']);   //问老师
        $this->redirect('home/login/login');
    }

}
