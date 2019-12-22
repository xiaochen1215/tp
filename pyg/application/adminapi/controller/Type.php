<?php

namespace app\adminapi\controller;

use think\Controller;
use think\Request;

class Type extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //查询所有数据
        $list = \app\common\model\Type::select();
        //返回数据
        $this->ok($list);
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
        //参数数组参考：
        // $params = [
        //     'type_name' => '手机',
        //     'spec' => [
        //         ['name' => '颜色', 'sort' => 50, 'value'=>['黑色', '白色', '金色']],
        //         ['name' => '内存', 'sort' => 50, 'value'=>['64G', '128G', '256G']],
        //     ],
        //     'attr' => [
        //         ['name' => '毛重', 'sort'=>50, 'value' => []],
        //         ['name' => '产地', 'sort'=>50, 'value' => ['进口', '国产']],
        //     ]
        // ]
        //参数检测
        $validate = $this->validate($params,[
            'type_name|模型名称' => 'require',
            'spec|规格'          => 'require|array',
            'attr|属性'          => 'require|array',
        ]);
        if($validate !== true){
            $this->fail($validate,400);
        }
        //添加数据  4个添加操作
        //使用事务
        //开启事务
        \think\DB::startTrans();
        try{
            //处理数据
            //添加type数据 添加商品模型信息
            $type = \app\common\model\Type::create($params,true);
            // $type = \app\common\model\Type::create(['type_name' => $params['type_name']]);
            //检测商品规格信息
            //添加模型下的商品规格信息
            //先对$params['spec']数据进行处理
            foreach( $params['spec'] as $k => $v ){
                //$k是下标 $v是值
                if(empty($v['name'])){
                    //如果规格名称为空 将当前整个规格名称信息删除  跳出本次遍历. 
                    unset($params['spec'][$k]);
                    continue;
                }
                //如果规格值不是数组 则删除当前整条数据
                if(!is_array($v['value'])){
                    //如果规格名称的规格值不是数组  将当前整个规格名称信息删除 跳出本次循环
                    unset($params['spec'][$k]);
                    continue;
                }
                //删除规格值数组中的空值
                foreach($v['value'] as $key => $value){
                    //将规格值中的空值删除
                    if(empty($value)){
                        unset($params['spec'][$k]['value'][$key]);
                        continue;
                    }
                }
                //如果规格值数组为空数组 删除整条数据
                if(empty($params['spec'][$k]['value'])){
                    //如果规格值为空 将当前整个规格名称信息删除
                    unset($params['spec'][$k]);
                    continue;
                }
            }
            //添加商品规格名称数据
            $spec_data = [];
            foreach($params['spec'] as $k => $v){
                //$type['id'] $v['name'] $v['sort']
                $spec_data[] = [
                    'type_id'   => $type['id'],
                    'spec_name' => $v['name'],
                    'sort'      => $v['sort']
                ];
            }
            //批量添加规格名称
            $spec_model = new \app\common\model\Spec();
            $spec_res = $spec_model->saveAll($spec_data);
            /*$spec_res结构参考：$spec_res = [
                0=>['id'=>100, 'spec_name' => '颜色'],
                1=>['id'=>101, 'spec_name' => '内存']
            ];*/
            /*$params['spec']结构参考：'spec' => [
                0=>['name' => '颜色', 'sort'=>'50', 'value' => ['金色', '白色', '黑色']],
                1=>['name' => '内存', 'sort'=>'60', 'value' => ['64G', '128G', '256G']],
            ],*/
            //添加商品规格值
            $spec_value_data = [];
            foreach($params['spec'] as $k => $v){
                //$v是一个规格名称数组
                foreach($v['value'] as $value){
                    $spec_value_data[] = [
                        'spec_id' => $spec_res[$k]['id'],
                        'type_id' => $type['id'],
                        'spec_value' => $value
                    ];
                }
            }
            $spec_value_model = new \app\common\model\SpecValue();
            $spec_value_model->saveAll($spec_value_data);
            //处理属性参数
            foreach($params['attr'] as $k => $v){
                if(empty($v['name'])){
                    //如果属性名称为空  则删除整条数据 属性信息
                    unset($params['attr'][$k]);
                    continue;
                }

                if(!is_array($v['value'])){
                    //如果属性可选值 不是数组 则设置为空数组
                    $v['value'] = [];
                }
                //如果属性值数组中有空值 则删除空值
                foreach($v['value'] as $key => $value){
                    //去除空的可选值
                    if(empty($value)){
                        unset($params['attr'][$k]['value'][$key]);
                    }
                }
            }
            
            //添加商品属性信息 到 属性表
            $attr_data = [];
            foreach($params['attr'] as $k => $v){
                $attr_data[] = [
                    'type_id' => $type['id'],
                    'attr_name' => $v['name'],
                    'soty' => $v['sort'],
                    'attr_values' => implode(',',$v['value']),
                ];
            }
            $attr_model = new \app\common\model\Attribute();
            $attr_model->saveAll($attr_data);

            //提交事务
            \think\Db::commit();
            //返回数据
            $this->ok($type);
        }catch(\Exception $e){
            //回滚事务
            \think\Db::rollback();
            //$this->fail('添加失败');
            $msg = $e->getMessage();
            $this->fail($msg);
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
        //嵌套关联
        $info = \app\common\model\Type::with('specs,specs.spec_values,attrs')->find($id);
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
        //接收参数
        $params = input();
        //参数检测
        $validate = $this->validate($params,[
            'type_name|模型名称' => 'require',
            'spec|规格' => 'require|array',
            'attr|属性' => 'require|array'
        ]);

        if($validate !== true){
            $this->fail($validate);
        }

        /*$params结构参考：$params = [
            'type_name' => '手机123',
            'spec' => [
                ['name' => '颜色', 'sort'=>'50', 'value' => ['金色', '白色', '黑色']],
                ['name' => '内存', 'sort'=>'60', 'value' => ['64G', '128G', '256G']],
            ],
            'attr' => [
                ['name' => '产地', 'sort'=>'50', 'value' => ['国产', '进口']],
                ['name' => '重量', 'sort'=>'60', 'value' => []],
            ]
        ];*/

         //开启事务
         \think\Db::startTrans();
         try{
             //类型名称的修改
             $type = \app\common\model\Type::update(['type_name'=>$params['type_name']], ['id'=>$id], true);
 
             //对规格数据进行处理（去除无效的数据，比如空的值）
             foreach($params['spec'] as $k=>$v){
                 if(empty($v['name'])){
                     //如果规格名称为空，删除整条数据
                     unset($params['spec'][$k]);
                     continue;
                 }
                 if(!is_array($v['value'])){
                     //如果规格值不是数组，删除整条数据
                     unset($params['spec'][$k]);
                     continue;
                 }
                 foreach($v['value'] as $key => $value){
                     if(empty($value)){
                         //去除 规格值数组中的空值
                         unset($params['spec'][$k]['value'][$key]);
                     }
                 }
                 //如果规格值数组是空数组，删除整条数据
                 if(empty($params['spec'][$k]['value'])){
                     unset($params['spec'][$k]);
                     continue;
                 }
             }
             //修改规格名称信息：先删除原来的数据，再新增新的数据
             \app\common\model\Spec::destroy(['type_id'=>$id]);
             //\app\common\model\Spec::where('type_id',$id)->delete();
             //组装数据
             $spec_data = [];
             foreach($params['spec'] as $v){
                 $spec_data[] = [
                     'spec_name' => $v['name'],
                     'sort' => $v['sort'],
                     'type_id' => $id
                 ];
             }
             $spec_model = new \app\common\model\Spec();
             $spec_res = $spec_model->saveAll($spec_data);
             /*$spec_res结构参考：$spec_res = [
                 ['id' => 101, 'spec_name' => '颜色'],
                 ['id' => 102, 'spec_name' => '内存'],
             ];*/
             //修改规格值信息：先删除原来的数据，再新增新的数据
             \app\common\model\SpecValue::destroy(['type_id'=>$id]);
             //组装数据
             $spec_value_data = [];
             foreach($params['spec'] as $k=>$v){
                 //内层遍历 规格值数组
                 foreach($v['value'] as $value){
                     $spec_value_data[] = [
                         'type_id' => $id,
                         'spec_id' => $spec_res[$k]['id'],
                         'spec_value' => $value
                     ];
                 }
             }
             $spec_value_model = new \app\common\model\SpecValue();
             $spec_value_model->saveAll($spec_value_data);
             //处理属性信息（去除空的属性值）
             foreach($params['attr'] as $k=>$v){
                 //如果属性名称为空，去除整条数据
                 if(empty($v['name'])){
                     unset($params['attr'][$k]);
                     continue;
                 }
                 //如果属性值数组不是数组，设置为空数组
                 if(!is_array($v['value'])){
                     $params['attr'][$k]['value'] = [];
                     continue;
                 }
                 //如果属性值为空的值，去除空的值
                 foreach($v['value'] as $key => $value){
                     if(empty($value)){
                         unset($params['attr'][$k]['value'][$key]);
                         continue;
                     }
                 }
             }
             //属性的修改：先删除原来的数据，再添加新的数据
             \app\common\model\Attribute::destroy(['type_id'=>$id]);
             $attr_data = [];
             foreach($params['attr'] as $k=>$v){
                 $attr_data[] = [
                     'attr_name' => $v['name'],
                     'attr_values' => implode(',', $v['value']),
                     'type_id' => $id,
                     'sort' => $v['sort']
                 ];
             }
             $attr_model = new \app\common\model\Attribute();
             $attr_model->saveAll($attr_data);
             //提交事务
             \think\Db::commit();
             $this->ok($type);
         }catch(\Exception $e){
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
        //删除4张表
        //开启事务
        \think\Db::startTrans();
        try{
            //商品模型下有商品 则不能删除
            $total = \app\common\model\Goods::where('type_id',$id)->count('id');
            if($total){
                //$this->fail('商品模型下有商品 不能删除');
                //改为抛出异常
                throw new \Exception('商品模型下有商品,不能删除');
            }
            //删除模型下的属性表数据
            \app\common\model\Attribute::destroy(['type_id'=>$id]);
            //删除模型下的规格名称表数据
            \app\common\model\Spec::destroy(['type_id'=>$id]);
            //删除模型下的规格值表数据
            \app\common\model\SpecValue::destroy(['type_id'=>$id]);
            //删除模型本身 删除type表
            \app\common\model\Type::destroy($id);
            //提交事务
            \think\Db::commit();
            $this->ok();
        }catch (\Exception $e){
            //回滚事务
            \think\Db::rollback();
            //返回错误提示
            $msg = $e->getMessage();
            $line= $e->getLine();
            $file= $e->getFile();
            $this->fail($msg . ';file:' . $file . ';line:' . $line);
        }
    }
}
