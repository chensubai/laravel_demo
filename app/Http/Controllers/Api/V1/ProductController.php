<?php
    
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\Request;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Brand;
use App\Models\Series;

class ProductController extends BaseController
{
    protected $BaseModels = 'App\Models\Product';
    protected $BaseAllVat = [];//获取全部验证
    protected $BAWhere  = [];//获取全部Where条件
    protected $BA  = ['*'];//获取全部选取字段 *是全部
    protected $BAOrder  = [['id','desc']];//获取全部字段排序

    protected $BaseLVat = [];//获取分页全部验证
    protected $BLWhere  = [
        'delete_status'=>['=',0],
        'publish_status'=>['=',1],
        'recommand_status'=>['=',''],
        'parent_series_id'=>['=',''],
        'series_id'=>['=',''],
        'name' => ['like',''],
        'product_sn'=> ['like',''],
    ];//获取全部分页Where条件
    protected $BL  = ['id','pic','name','name_cn','brand_name','publish_status','recommand_status','product_sn','sale','price'];//获取全部分页选取字段 *是全部
    protected $BLOrder  = [['sort','desc']];//获取全部分页字段排序


    protected $BaseOneVat = ['id' => 'required',];//单个处理验证
    protected $BOWhere = ['id'=>['=','']];//单个查询验证
    protected $BO = ['*'];//单个选取字段；*是全部
    protected $BOOrder = [['id','desc']];//单个选取字段排序

    protected $BaseCreateVat = [
        'name' => 'required',//系列名称
        'name_cn' => 'required',//系列名称（中文）
        'brand_id' => 'required',//品牌的id
        'category_id' => 'required',//产品类型的id,
    ];//新增验证
    protected $BaseCreate =[
        'brand_id'=>'',
        'product_category_id'=>'',
        'feight_template_id'=>'',
        'product_attribute_category_id'=>'',
        'name'=>'','name_cn'=>'',
        'pic'=>'','product_sn'=>'','delete_status'=>'',
        'publish_status'=>'','new_status'=>'','recommand_status'=>'',
        'verify_status'=>'','sort'=>'','sale'=>'',
        'price'=>'',
        'promotion_price'=>'',
        'gift_growth' =>'',
        'gift_point'=>'',
        'use_point_limit' =>'',
        'sub_title' =>'',
        'description' =>'',
        'original_price'=>'',
        'stock' =>'',
        'low_stock' =>'',
        'unit' =>'',
        'weight' =>'',
        'preview_status'=>'',
        'service_ids'=>'',
        'keywords'=>'',
        'keywords_cn' =>'',
        'note'=>'',
        'album_pics'=>'',
        'album_pics_exhibition' =>'',
        'album_pics_wear' =>'',
        'album_pics_details' =>'',
        'detail_desc' =>'',
        'detail_desc_cn' =>'',
        'promotion_start_time'=>'',
        'promotion_end_time' =>'',
        'promotion_per_limit' =>'',
        'promotion_type' =>'',
        'brand_name'=>'',
        'product_category_name' =>'',
        'parent_series_id' =>'',
        'series_id' =>'',
        'postage' =>'',
        'eligibility' =>'',
        'release_time'=>'',
        'date_type' =>'',
        'advance_charge' =>'',
        'interview_num_week' =>'',
        'interview_num' =>'',
        'water_sale' =>'',
        'ding_stock' =>'',
        'new_sale_img' =>'',
        'new_sale_img_cn'=>'',
        'active_img' =>'',
        'active_img_cn' =>'',
        'create_date' => ['type','date'],
    ];//新增数据

    protected $BaseUpdateVat = [
        'id' =>        'required',
        // 'parent_id' => 'required',//上级ID
        // 'order' => 'required',//菜单排序
        // 'title' => 'required',//标题
        // 'icon' => 'required',//图标
        // 'uri' => 'required',//URI
        // 'routes' => 'required',//路由
    ];//新增验证
    protected $BaseUpdate =[
        'id'=>'',
        'brand_id'=>'',
        'product_category_id'=>'',
        'feight_template_id'=>'',
        'product_attribute_category_id'=>'',
        'name'=>'','name_cn'=>'',
        'pic'=>'','product_sn'=>'','delete_status'=>'',
        'publish_status'=>'','new_status'=>'','recommand_status'=>'',
        'verify_status'=>'','sort'=>'','sale'=>'',
        'price'=>'',
        'promotion_price'=>'',
        'gift_growth' =>'',
        'gift_point'=>'',
        'use_point_limit' =>'',
        'sub_title' =>'',
        'description' =>'',
        'original_price'=>'',
        'stock' =>'',
        'low_stock' =>'',
        'unit' =>'',
        'weight' =>'',
        'preview_status'=>'',
        'service_ids'=>'',
        'keywords'=>'',
        'keywords_cn' =>'',
        'note'=>'',
        'album_pics'=>'',
        'album_pics_exhibition' =>'',
        'album_pics_wear' =>'',
        'album_pics_details' =>'',
        'detail_desc' =>'',
        'detail_desc_cn' =>'',
        'promotion_start_time'=>'',
        'promotion_end_time' =>'',
        'promotion_per_limit' =>'',
        'promotion_type' =>'',
        'brand_name'=>'',
        'product_category_name' =>'',
        'parent_series_id' =>'',
        'series_id' =>'',
        'postage' =>'',
        'eligibility' =>'',
        'release_time'=>'',
        'date_type' =>'',
        'advance_charge' =>'',
        'interview_num_week' =>'',
        'interview_num' =>'',
        'water_sale' =>'',
        'ding_stock' =>'',
        'new_sale_img' =>'',
        'new_sale_img_cn'=>'',
        'active_img' =>'',
        'active_img_cn' =>'',
        'modify_date' => ['type','date'],
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据

    public function _oneFrom($RData)
    {
        if(!empty($RData)){
            $RData['list'] = DB::table('pms_sku_stock')
                            ->where('product_id','=',$RData['id'])
                            ->where('is_delete','=',0)
                            ->orderBy('sort','desc')
                            ->get();
        }
        return $RData;
    }

    public function  brandLimit(Request $request){

        $BaseLVat =[];
        $BLWhere  = [];
        $where_data = ['show_status'=>['=',1]];
        $BL=['*'];
        $BLOrder = [['sort','desc']];
        $validator = Validator::make($request->all(), $BaseLVat);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        $cur_page= !empty($data['cur_page'])? $data['cur_page']: 1;
        $size= !empty($data['size'])? $data['size']: 10;
        //修改参数  request要存在或者BUWhere存在
        foreach($BLWhere as $B_k => $B_v){
            // var_dump($B_k[1]);exit;
            if(!empty($B_v) && empty($B_v[1]) && !empty($data[$B_k])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && isset($B_v[1]) && $B_v[1] !=''){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $RData = (new Brand)->BaseLimit($where_data,$BL,$BLOrder,$cur_page,$size);
        return  $this->success($RData,__('base.success'));
    }

    public function  seriesLimit(Request $request){

        $BaseLVat =[];
        $BLWhere  = ['brand_id'=>['=','']];
        $where_data = ['show_status'=>['=',1]];
        $BL=['*'];
        $BLOrder = [['sort','desc']];
        $validator = Validator::make($request->all(), $BaseLVat);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        $cur_page= !empty($data['cur_page'])? $data['cur_page']: 1;
        $size= !empty($data['size'])? $data['size']: 10;
        //修改参数  request要存在或者BUWhere存在
        foreach($BLWhere as $B_k => $B_v){
            // var_dump($B_k[1]);exit;
            if(!empty($B_v) && empty($B_v[1]) && !empty($data[$B_k])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && isset($B_v[1]) && $B_v[1] !='' ){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $RData = (new Series)->BaseLimit($where_data,$BL,$BLOrder,$cur_page,$size);
        return  $this->success($RData,__('base.success'));
    }

    /**
     *热门系列
     *
     * @param Request $request
     * @return void
     */
    public function  seriesAll(Request $request)
    {
        $category_id = !empty($request->category_id) ? $request->category_id : 0;
        $RData = (new Series)->seriesClass($category_id);
        return  $this->success($RData,__('base.success'));
    }
}

