<?php

namespace app\home\controller;

use think\Controller;
use think\Request;

class Order extends Base
{
    //显示结算页面
    //结算页面展示
    //结算页需要登录才能访问，修改order控制器create方法，判断登录，并设置返回的url
    public function create()
    {   
        //登陆检测 
        if(!session('?user_info')){
            //没有登陆 跳转到登陆页面
            //设置登陆成功后的跳转地址
            //session('back_url','home/order/create')
            session('back_url','home/cart/index');
            $this->redirect('home/login/login');
        }
        // dump('xxxx');die;
        //获取收货地址信息
        //获取用户id
        $user_id = session('user_info.id');
        // var_dump($user_id);die;
        $address = \app\common\model\Address::where('user_id',$user_id)->select();
        // dump($address);die;
        //查询选中的购物记录
        $res = \app\home\logic\OrderLogic::getCartWithGoods();

        //查询的数据分为以下三部分
        //写法1
        $cart_data = $res['cart_data'];
        $total_price = $res['total_price'];
        $total_number = $res['total_number'];
        
        return view('create',['address' => $address,'cart_data' => $cart_data,'total_price' => $total_price,'total_number' => $total_number]);
    }

        /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //接收参数
        $params = input();
        // var_dump($params);die;
        //参数检测
        $validate = $this->validate($params,[
            'address_id' => 'require|integer|gt:0'
        ]);
        if($validate !== true){
            $this->error($validate);
        }
        //查询收货地址信息
        $user_id = session('user_info.id');
        $address = \app\common\model\Address::where('user_id',$user_id)->where('id',$params['address_id'])->find();
        if(!$address){
            $this->error('收货地址数据异常');
        }
        //开启事务
        \think\Db::startTrans();
        try{
            //向订单表添加一条数据
            //订单编号 纯数字或者字母加数字
            $order_sn = time() . mt_rand(100000,9999999);
            //查询选中的购物记录  计算商品总价
            $res = \app\home\logic\OrderLogic::getCartWithGoods();
            //$res['cart_data']  $res['total_price'] $res['total_number']
            // dump($res);die;

            //库存检测
            foreach($res['cart_data'] as $v){
                //购买数量$v['number'] 库存$v['goods']['goods_number']
                if($v['number'] > $v['goods']['goods_number']){
                    //订单中包含库存不足的商品
                    //抛出异常 直接进入catch语法
                    throw new \Exception('订单中包含库存不足的商品');
                }
            }

            //组装一条订单数据
            $row = [
                'user_id' => $user_id,
                'order_sn'=> $order_sn,
                'order_status' => 0,
                'consignee' => $address['consignee'],
                'phone' => $address['phone'],
                'address' => $address['area'] . $address['address'],
                'goods_price' => $res['total_price'], //商品总价
                'shipping_price' => 0,  //邮费
                'coupon_price' => 0,   //优惠券抵扣
                'order_amount' => $res['total_price'], // 商品总价 + 邮费 - 优惠劵抵扣
                'total_amount' => $res['total_price'], // 商品总价 + 邮费
            ];
            //添加一条数据
            $order = \app\common\model\Order::create($row,true);
            // dump($order);die;
            //向订单商品表添加多条数据
            $order_goods = [];
            foreach($res['cart_data'] as $v){
                //将每条购物记录  添加到订单商品表
                $order_goods[] = [
                    'order_id'         => $order['id'],
                    'goods_id'         => $v['goods_id'],
                    'spec_goods_id'    => $v['spec_goods_id'],
                    'number'           => $v['number'],
                    'goods_name'       => $v['goods']['goods_name'],
                    'goods_logo'       => $v['goods']['goods_logo'],
                    'goods_price'      => $v['goods']['goods_price'],
                    'spec_value_names' => $v['spec_goods']['value_names'],
                ];
            }
            //批量添加数据
            $order_goods_model = new \app\common\model\OrderGoods();
            $order_goods_model->saveAll($order_goods);

            //从购物车表删除对应记录
            \app\common\model\Cart::destroy(['user_id'=>$user_id,'is_selected'=>1]);

            //冻结库存
            $goods = [];
            $spec_goods = [];
            foreach($res['cart_data'] as $v){
                //购买数量  $v['number'] $v['goods_id'] $v['spec_goods_id']
                //下单前的库存 $v['goods']['goods_number']  $v['goods']['frozen_number']
                if($v['spec_goods_id']){
                    //冻结SKU库存  //SKU库存 store_count  冻结库存 store_frozen
                    $spec_goods[] = [
                        'id'          => $v['spec_goods_id'],
                        'store_count' => $v['goods']['goods_number']  - $v['number'],
                        'store_frozen'=> $v['goods']['frozen_number'] + $v['number']
                    ];
                }else{
                    //冻结商品库存 //商品库存goods_numer 冻结库存frozen_number
                    $goods[] = [
                        'id' => $v['goods_id'],
                        'goods_number'=> $v['goods']['goods_number'] - $v['number'],
                        'frozen_number' => $v['goods']['frozen_number'] + $v['number']
                    ];
                }
            }
            //批量修改库存
            $goods_model = new \app\common\model\Goods();
            $goods_model->saveAll($goods);
            $spec_goods_model = new \app\common\model\SpecGoods();
            $spec_goods_model->saveAll($spec_goods);

            //提交事务
            \think\Db::commit();
            //接下来显示 选择支付方式的页面
        }catch(\Exception $e){
            //回滚事务
            \think\Db::rollback();
            $msg = $e->getMessage();
            $this->error($msg);
        }

        //跳转到pay方法
        $this->redirect( 'home/order/pay?id=' . $order['id'] );

    }


    //选择支付方式   int $id  
    public function pay($id){
        //查询订单信息 
        $order = \app\common\model\Order::find($id);
        //支付方式
        $pay_type = config('pay_type');
        return view('pay',['order' => $order, 'pay_type' => $pay_type]);
    }

    public function callback(){
        $params = input();
        dump($params);
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
    }


    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
