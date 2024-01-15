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

class ShoppingController extends BaseController
{
    protected $BaseModels = 'App\Models\Shopping';
    protected $BaseAllVat = [];//获取全部验证
    protected $BAWhere  = ['status'=>['=',1]];//获取全部Where条件
    protected $BA  = ['*'];//获取全部选取字段 *是全部
    protected $BAOrder  = [['id','desc']];//获取全部字段排序

    protected $BaseLVat = [];//获取分页全部验证
    protected $BLWhere  = [];//获取全部分页Where条件
    protected $BL  = ['*'];//获取全部分页选取字段 *是全部
    protected $BLOrder  = [['id','desc']];//获取全部分页字段排序

    protected $BaseOneVat = ['id' => 'required',];//单个处理验证
    protected $BOWhere = ['id'=>['=','']];//单个查询验证
    protected $BO = ['*'];//单个选取字段；*是全部
    protected $BOOrder = [['id','desc']];//单个选取字段排序

    protected $BaseCreateVat = [
        'title' => 'required',//
    ];//新增验证

    protected $BaseCreate = [];//新增数据

    protected $BaseUpdateVat = [
        'id' =>        'required',
    ];//新增验证
    protected $BaseUpdate =[
        'id'=>'',
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据

    public function _oneFrom($RData)
    {
        if(!empty($RData)){
            $RData['product'] = DB::table('pms_product')
                            ->where('id','=',$RData['product_id'])
                            ->first();
            $RData['sku_stock'] = DB::table('pms_sku_stock')
                            ->where('id','=',$RData['sku_id'])
                            ->first();
        }
        return $RData;
    }
    /**
     * 加入购物车
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        $create_data = [];
        $validator = Validator::make(
            $request->all(),
            [
                'product_id' => 'required',//商品id
                'sku_id' => 'required',//sku_id
                'num' => 'required',//数量
            ],
            [
                'product_id.required' => __('api.shop.create.product_id.required'),//商品id
                'sku_id.required' => __('api.shop.create.product_id.required'),//sku_id
                'num.required' => __('api.shop.create.product_id.required'),//数量
            ]
        );
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $user_id = Auth::id();
        $username  = DB::table('member')->where('id','=',$user_id)->pluck('username');
        $username  =  !empty($username[0]) ? $username[0]: '';

        $shopping_cart  = DB::table('shopping_cart')
        ->where('product_id','=',$request->product_id)
        ->where('sku_id','=',$request->sku_id)
        ->where('user_id','=',$user_id)->first();
        // var_dump($shopping_cart);exit;
        $pms_sku_stock  = DB::table('pms_sku_stock')
        ->where('id','=',$request->sku_id)
        ->first();
        if(empty($pms_sku_stock) || $pms_sku_stock ==NULL){
            return  $this->error();
        }else{
            (int)$numLimit = $pms_sku_stock->stock;
            (int)$num = $request->num;
        }

        if(empty($shopping_cart) || $shopping_cart ==NULL){
            if($num  >  $numLimit) return  $this->error(__('base.shopping_cart.error'));
            $data = [
                'product_id' => $request->product_id,
                'sku_id' => $request->sku_id,
                'user_id' => $user_id,
                'username' => $username,
                'num' => $num,
                'createtime' => time()
            ];
            //新建
            $id = (new $this->BaseModels)->BaseCreate($data);
        }else{

            (int)$shopping_num  = $shopping_cart->num;
            $num+=$shopping_num;
            if($num  >  $numLimit) return  $this->error(__('base.shopping_cart.error'));
            //编辑
            DB::table('shopping_cart')
            ->where('product_id','=',$request->product_id)
            ->where('sku_id','=',$request->sku_id)
            ->where('user_id','=',$user_id)->update(['num'=>$num]);
            $id = $pms_sku_stock->id;
        }
        $data['id'] = $id;
        return  $this->success($data,__('base.success'));
    }

     /**
     *基础取多个值带分页方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseLimit(Request $request){
        $where_data= [];
        $validator = Validator::make($request->all(), $this->BaseLVat);
        if ($validator->fails()) return $this->errorBadRequest($validator->messages);
        $data = $request->all();
        $cur_page= !empty($data['cur_page'])? $data['cur_page']: 1;
        $size= !empty($data['size'])? $data['size']: 10;
        //修改参数  request要存在或者BUWhere存在
        foreach($this->BLWhere as $B_k => $B_v){
            // var_dump($B_k[1]);exit;
            if(!empty($B_v) && empty($B_v[1]) && !empty($data[$B_k])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }

            if(!empty($B_v) && isset($B_v[1]) && $B_v[1] !==''){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $user_id = Auth::id();
        $where_data[] = ['user_id','=',$user_id ];
        $RData = (new $this->BaseModels)->BaseLimit($where_data,$this->BL,$this->BLOrder,$cur_page,$size);
        if(!empty($RData['data'])){
            foreach($RData['data'] as &$v){
                if(!empty($RData))
                {
                    $sku_stock  = DB::table('pms_sku_stock')
                    ->where('id','=',$v['sku_id'])
                    ->first();

                    $product = DB::table('pms_product')
                    ->where('id','=',$v['product_id'])
                    ->first();

                    $v['sku_stock'] =  $sku_stock;
                    $v['product'] = $product;
                }
            }
        }
        return  $this->success($RData,__('base.success'));
    }

}
