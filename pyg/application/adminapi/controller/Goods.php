<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Goods extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //分页 加 搜索
        $params = input();
        $where = [];
        if(!empty($params['keyword'])){
            $keyword = $params['keyword'];
            $where['goods_name'] = ['like',"%{$keyword}%"];
        }
        //分页查询
        $list = \app\common\model\Goods::with('type_bind,brand_bind,category_bind')->where($where)->order('id desc')->paginate(10);
        //返回数据
        $this->ok($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
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
        //参数检测
        $validate = $this->validate($params,[
            'goods_name|商品名称' => 'require',
            'goods_price|商品价格' => 'require|float|gt:0',
            'goods_number|商品库存'=> 'require|integer|gt:0',
            //省略了很多字段
            'goods_logo|logo图片' => 'require',
            'cate_id|商品分类' => 'require|integer|gt:0',
            'brand_id|商品品牌' => 'require|integer|gt:0',
            'type_id|商品模型' => 'require|integer|gt:0',
            'goods_images|相册图片' => 'require|array',
            'item|规格商品' => 'require|array',
            'attr|属性值' => 'require|array',
        ],[
            'goods_price.float' => '商品价格必须是整数或者小数'
        ]);
        if($validate !== true){
            $this->fail($validate);
        }

        //防范XSS攻击 对富文本编辑器字段 做过滤处理
        $params['goods_desc'] = input('goods_desc','','remove_xss');
            
        //添加数据
        //开始事务
        \think\Db::startTrans();
        try{
            //商品表数据(SPU表)
            //logo图片生成缩略图
            if(is_file('.' . $params['goods_logo'])){
                //直接覆盖写法
                //\think\Image::open('.' . $params['goods_logo'])->thumb(200,240)->save('.' . $params['goods_logo']);
                //重新命名的写法
                $goods_logo = dirname($params['goods_logo']) . DS . 'thumb_' . basename($params['goods_logo']);
                \think\Image::open('.' . $params['goods_logo'])->thumb(200,240)->save('.' . $goods_logo);
                $params['goods_logo'] = $goods_logo;
            }else{
                $this->fail('商品logo图片不存在');
            }

            //商品属性转化为json字符串    防止中文被转码
            $params['goods_attr'] = json_encode($params['attr'],JSON_UNESCAPED_UNICODE);
            //$params['goods_attr'] = json_encode(array_values($params['attr']),JSON_UNESCAPED_UNICODE);

            //添加商品数据
            $goods = \app\common\model\Goods::create($params,true);

            //商品相册图片生成缩略图 $params['goods_images']
            $goods_images = [];
            foreach($params['goods_images'] as $img){
                //生成缩略图 分别生成最大宽高800*800  400*400的缩略图
                $pic_big = dirname($img) . DS . 'thumb_800_' . basename($img);
                $pic_sma = dirname($img) . DS . 'thumb_400_' . basename($img);
                //生成缩略图
                $image = \think\Image::open('.' . $img);
                $image->thumb(800,800)->save('.' . $pic_big); //先生成大尺寸图片
                $image->thumb(400,400)->save('.' . $pic_sma);
                $goods_images[] = [
                    'goods_id' => $goods['id'],
                    'pics_big' => $pic_big,
                    'pics_sma' => $pic_sma,
                ];

            }
            //添加相册数据
            $images_model = new \app\common\model\GoodsImages();
            $images_model->saveAll($goods_images);
            //组装规格商品sku数据
            $spec_goods = [];
            foreach($params['item'] as $v){
                //$v是一条sku数据 缺少goods_id字段 $v和数据表字段对比 缺少goods_id
                $v['goods_id'] = $goods['id'];
                $spec_goods[] = $v;
            }
            //添加sku数据
            $spec_goods_model = new \app\common\model\SpecGoods();
            $spec_goods_model->saveAll($spec_goods);
            //提交事务
            \think\Db::commit();
            //重新查询商品数据 关联模型查询
            $info = \app\common\model\Goods::with('type_bind,brand_bind,category_bind')->find($goods['id']);
            //返回数据
            $this->ok($info);
        }catch (\Exception $e){
            //回滚事务
            \think\Db::rollback();
            $msg = $e->getMessage();
            $this->fail($msg);
            //$this->fail('操作失败');
        }

    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
      //查询商品一条数据  相册图片、规格商品sku、所属分类、所属品牌
      $info = \app\common\model\Goods::with('goods_images,spec_goods,category,brand')->find($id);
      //$info = \app\common\model\Goods::with('goods_images,spec_goods,category,brand,type,type.attrs,type.specs,type.specs.spec_values')->find($id); //这里面的 type.attrs,type.specs,type.specs.spec_values 都是嵌套，不支持
      //关联模型查询，不允许多个嵌套关联，只能有一个生效
      $info['type'] = \app\common\model\Type::with('attrs,specs,specs.spec_values')->find($info['type_id']);
      $this->ok($info);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //查询商品相关信息(商品/所属分类/相册图片/规格商品SKU/分类下的所属品牌)
        $info = \app\common\model\Goods::with('goods_images,category,brand,spec_goods')->find($id);
        //关联模型查询 不允许多个嵌套关联 只能有一个生效 查询所属模型及规格属性
        $type = \app\common\model\Type::with('attrs,specs,specs.spec_values')->find($info['type_id']);
        $info['type'] = $type;
        //查询所有的商品模型 type
        $type = \app\common\model\Type::field('id,type_name')->select();
        //查询商品分类 用于三级联动中三个下拉列表显示
        //查询所有的一级分类
        $category_one = \app\common\model\Category::where('pid',0)->select();
        //查询所属的一级分类下的所有二级分类 找到商品所属的一级分类id 和二级分类id
        $pid_path = $info['category']['pid_path']; //[0_3_124]
        //所属一级分类ID $temp[1];所属的二级分类id $temp[2]
        $category_two = \app\common\model\Category::where('pid',$pid_path[1])->select();
        //查询所属的二级分类下的所有三级分类
        $category_three = \app\common\model\Category::where('pid',$pid_path[2])->select();
        //组装返回结果
        $data = [
            'goods' => $info,
            'type' => $type,
            'category' => [
                'cate_one' => $category_one,
                'cate_two' => $category_two,
                'cate_three' => $category_three,
            ],
            
        ];
        $this->ok($data);
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
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'goods_name|商品名称' => 'require',
            'goods_price|商品价格' => 'require|float|gt:0',
            'goods_number|商品库存'=> 'require|integer|gt:0',
            //省略了很多字段
            // 'goods_logo|logo图片' => 'require',
            'cate_id|商品分类' => 'require|integer|gt:0',
            'brand_id|商品品牌' => 'require|integer|gt:0',
            'type_id|商品模型' => 'require|integer|gt:0',
            'goods_images|相册图片' => 'array',
            'item|规格商品' => 'require|array',
            'attr|属性值' => 'require|array',
        ],[
            'goods_price.float' => '商品价格必须是整数或者小数'
        ]);
        if($validate !== true){
            $this->fail($validate);
        }
        // //logo图片生成缩略图
        // if(!file_exists('.' . $params['goods_logo'])){
        //     //图片不存在  则报错提示
        //     $this->fail('logo图片不存在');
        // }
        //开始事务

        //防范XSS攻击 对富文本编辑器字段 做过滤处理
        $params['goods_desc'] = input('goods_desc','','remove_xss');

        \think\Db::startTrans();
        try{
            //商品表数据(SPU表)
            //logo图片生成缩略图
            if(!empty($params['goods_logo']) && is_file('.' . $params['goods_logo'])){
                //直接覆盖写法
                //\think\Image::open('.' . $params['goods_logo'])->thumb(200,240)->save('.' . $params['goods_logo']);
                //重新命名的写法
                $goods_logo = dirname($params['goods_logo']) . DS . 'thumb_' . basename($params['goods_logo']);
                \think\Image::open('.' . $params['goods_logo'])->thumb(200,240)->save('.' . $goods_logo);
                $params['goods_logo'] = $goods_logo;
            }

            //商品属性转化为json字符串    防止中文被转码
            $params['goods_attr'] = json_encode($params['attr'],JSON_UNESCAPED_UNICODE);
            //$params['goods_attr'] = json_encode(array_values($params['attr']),JSON_UNESCAPED_UNICODE);
            
            //修改商品数据
            \app\common\model\Goods::update($params,['id'=>$id],true);

            //商品相册图片生成缩略图 $params['goods_images']
            if(!empty($params['goods_images'])){
                $goods_images = [];
                foreach($params['goods_images'] as $img){
                    //生成缩略图 分别生成最大宽高800*800  400*400的缩略图
                    $pic_big = dirname($img) . DS . 'thumb_800_' . basename($img);
                    $pic_sma = dirname($img) . DS . 'thumb_400_' . basename($img);
                    //生成缩略图
                    $image = \think\Image::open('.' . $img);
                    $image->thumb(800,800)->save('.' . $pic_big); //先生成大尺寸图片
                    $image->thumb(400,400)->save('.' . $pic_sma);
                    $goods_images[] = [
                        'goods_id' => $id,
                        'pics_big' => $pic_big,
                        'pics_sma' => $pic_sma,
                    ];
    
                }
                //添加相册数据
                $images_model = new \app\common\model\GoodsImages();
                $images_model->saveAll($goods_images);
            }

            //组装规格商品sku数据
            //先删除原来的数据再添加新的数据
            \app\common\model\SpecGoods::destroy(['goods_id'=>$id]);
            $spec_goods = [];
            foreach($params['item'] as $v){
                //$v是一条sku数据 缺少goods_id字段 $v和数据表字段对比 缺少goods_id
                $v['goods_id'] = $id;
                $spec_goods[] = $v;
            }
            //添加sku数据
            $spec_goods_model = new \app\common\model\SpecGoods();
            $spec_goods_model->saveAll($spec_goods);
            //提交事务
            \think\Db::commit();
            //重新查询商品数据 关联模型查询
            $info = \app\common\model\Goods::with('type_bind,brand_bind,category_bind')->find($id);
            //返回数据
            $this->ok($info);
        }catch (\Exception $e){
            //回滚事务
            \think\Db::rollback();
            $msg = $e->getMessage();
            $this->fail($msg);
            //$this->fail('操作失败');
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //删除商品数据
        // \app\common\model\Goods::destroy($id);
        //判断商品是否已上架
        // $goods = \app\common\model\Goods::find($id);
        // if(empty($goods)){
        //     $this->fail('数据异常,商品已经不存在');
        // }
        // if($goods['is_on_sale']){
        //     //上架中  无法删除
        //     $this->fail('上架中,无法删除');
        // }
        // //删除
        // $goods->delete();
        // $this->ok();

        $is_on_sale = \app\common\model\Goods::where('id',$id)->value('is_on_sale');
        if($is_on_sale){
            $this->fail('商品已上架,不能删除');
        }
        \app\common\model\Goods::destroy($id);

        //删除相册图片(从数据表删除 从硬盘删除)
        //查询相册图片
        $goods_images = \app\common\model\GoodsImages::where('goods_id',$id)->select();
        //从数据表删除相册图片
        \app\common\model\GoodsImages::destroy(['goods_id'=>$id]);
        //从硬盘删除图片
        foreach($goods_images as $v){
            //$v['pics_big'] $v['pics_sma']
            if(is_file('.' . $v['pics_big'])){
                unlink('.' . $v['pics_big']);
            }
            if(is_file('.' . $v['pics_sma'])){
                unlink('.' . $v['pics_sma']);
            }
            
        }
        $this->ok();
    }

    //删除相册图片接口
    public function delpics($id){
        //直接删除图片记录
        //\app\common\model\GoodsImages::destroy($id);
        $info = \app\common\model\GoodsImages::find($id);
        if(!$info){
            $this->ok();
        }
        //删除图片记录
        $info->delete();
        //并从磁盘删除图片文件 $info['pics_big']  $info['pics_sma']
        if(file_exists($info['pics_big'])){
            unlink('.' . $info['pics_big']);
        }
        if(file_exists($info['pics_sma'])){
            unlink('.' . $info['pics_sma']);
        }
        $this->ok();
    }
}
