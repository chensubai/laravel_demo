<?php
    
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\Request;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\SkuStock;

class OrderController extends BaseController
{
    protected $BaseModels = 'App\Models\Order';
    protected $BaseAllVat = [];//获取全部验证
    protected $BAWhere  = [];//获取全部Where条件
    protected $BA  = ['*'];//获取全部选取字段 *是全部
    protected $BAOrder  = [['id','desc']];//获取全部字段排序

    protected $BaseLVat = ['order_type' => 'required',];//获取分页全部验证
    protected $BLWhere  = ['order_type'=>['=',''],'status'=>['=',''],'order_num'=>['like','']];//获取全部分页Where条件
    protected $BL  = ['*'];//获取全部分页选取字段 *是全部
    protected $BLOrder  = [['id','desc']];//获取全部分页字段排序

    protected $BaseOneVat = ['id' => 'required',];//单个处理验证
    protected $BOWhere = ['id'=>['=','']];//单个查询验证
    protected $BO = ['*'];//单个选取字段；*是全部
    protected $BOOrder = [['id','desc']];//单个选取字段排序

    protected $BaseCreateVat = [
        'title' => 'required',//
    ];//新增验证
    protected $BaseCreate =[
        'title'=>'',
    ];//新增数据

    protected $BaseUpdateVat = [
        'id' =>        'required',
    ];//新增验证
    protected $BaseUpdate =[
        'id'=>'',
        'overdue'=>'','serialnum2'=>'','serialnum'=>'',
        'status'=>'','shoptime'=>'',
        // 'updatetime' => ['type','time'],
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据

    public function addOrder(Request $request){

        $create_data = [];
        $validator = Validator::make(
            $request->all(),
            [
                'shopping_cart_ids' => 'required',//商品id
                'shop_id' => 'required',
                // 'shop_name' => 'required',
                'order_type' => 'required',
            ],
            [
                'shopping_cart_ids.required' => __('api.order.addOrder.shopping_cart_ids.required'),
                'shop_id.required' => __('api.order.addOrder.shop_id.required'),
                // 'shop_name.required' => __('api.order.addOrder.shop_name.required'),
                'order_type.required' => __('api.order.addOrder.order_type.required'),
            ]
        );
        if ($validator->fails()) return $this->errorBadRequest($validator);

        $user_id = Auth::id();
        $username  = DB::table('member')->where('id','=',$user_id)->pluck('username');
        $username  =  !empty($username[0]) ? $username[0]: '';
        $shopping_cart_id = explode(',',$request->shopping_cart_ids);
        $shopping_cart  = DB::table('shopping_cart')->whereIn('id',$shopping_cart_id)->get();
        $shop_name  = DB::table('shop')->where('id','=',$request->shop_id)->pluck('shop_name');
        $shop_name  =  !empty($shop_name[0]) ? $shop_name[0]: '';
        //验证
        if(empty($shopping_cart) || $shopping_cart ==NULL) return  $this->error(__('base.shopping_cart.error'));
        $orderData = [];
        $reinsurance = [];
        $stock = [];
        $num = 0;//总数量
        $price = 0;//总金额
        $order_num = date('YmdHis').rand(1000,2000);
        $orderData = [
            'order_num' => $order_num,
            'username' => $username,
            'userid' => $user_id,
            'shop_id' => $request->shop_id,
            'shop_name' =>  $shop_name,
            'shoptime' => empty($request->shoptime) ? 0 :$request->shoptime,
            'order_type' =>$request->order_type,
            'createtime' => time(),
        ];

        foreach($shopping_cart as $v){
            $sku_stock = DB::table('pms_sku_stock')->where('id','=',$v->sku_id)->first();
            $product = DB::table('pms_product')->where('id','=',$v->product_id)->first();
            if(empty($sku_stock) || $sku_stock== NULL ||empty($product) || $product== NULL)  return  $this->error(__('base.shopping_cart.error'));
            (int)$shopping_num = $v->num;
            (int)$sku_stock_num = $sku_stock->stock;
            if($shopping_num > $sku_stock_num)  return  $this->error(__('base.shopping_cart.error'));

            $num+= $shopping_num;
            (float)$sku_stock_price = $sku_stock->price;
            $price+= $sku_stock_price;
            $stock[] = ['id' =>$v->product_id,'stock'=>$sku_stock_num-$shopping_num];
            $reinsuranceSon = [
                'order_num' => $order_num,
                'num' => $v->num,
                'product_id' => $v->product_id,
                'sku_id' => $v->sku_id,
                'price' => $sku_stock->price,
                'goodsname' => $product->name,
                'goodsimage' => $product->pic,
                'itemno' => $product->product_sn,
                'username' => $username,
                'user_id' => $user_id,
                'createtime' => time()
            ];
            $reinsurance[] = $reinsuranceSon;
        }
        $orderData['num'] = $num;
        $orderData['price'] = $price;
        // var_dump($orderData);exit;
        DB::beginTransaction();
        //加订单减库存
        $orders = DB::table('orders')->insert($orderData);//创订单
        $orders_reinsurance = DB::table('orders_reinsurance')->insert($reinsurance);//创分单
        $pms_sku_stock = (new SkuStock)->updateBatch($stock);//减库存
        $shopping_cart = DB::table('shopping_cart')->whereIn('id',$shopping_cart_id)->delete(); //删购物车
        // var_dump($orders );
        // var_dump( $orders_reinsurance);
        // var_dump( $pms_sku_stock );
        // var_dump($shopping_cart);
        if($orders && $orders_reinsurance && $pms_sku_stock && $shopping_cart){
            DB::commit();
            return $this->success();
        }else{
            DB::rollback();
            return $this->error();
        }
    }

    public function _oneFrom($RData)
    {
        if(!empty($RData)){
            $RData['list'] = DB::table('orders_reinsurance')
                            ->where('order_num','=',$RData['order_num'])
                            ->orderBy('id','desc')
                            ->get();
        }
        return $RData;
    }
}
