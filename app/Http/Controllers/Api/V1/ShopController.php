<?php
    
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Admin\V1\BaseController;
use Illuminate\Http\Request;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;

class ShopController extends BaseController
{
    protected $BaseModels = 'App\Models\Shop';
    protected $BaseAllVat = [];//获取全部验证
    protected $BAWhere  = ['status'=>['=',1]];//获取全部Where条件
    protected $BA  = ['*'];//获取全部选取字段 *是全部
    protected $BAOrder  = [['id','desc']];//获取全部字段排序

    protected $BaseLVat = [];//获取分页全部验证
    protected $BLWhere  = ['status'=>['=',1]];//获取全部分页Where条件
    protected $BL  = ['*'];//获取全部分页选取字段 *是全部
    protected $BLOrder  = [['id','desc']];//获取全部分页字段排序

    protected $BaseOneVat = ['id' => 'required',];//单个处理验证
    protected $BOWhere = ['id'=>['=','']];//单个查询验证
    protected $BO = ['*'];//单个选取字段；*是全部
    protected $BOOrder = [['id','desc']];//单个选取字段排序

    protected $BaseCreateVat = [
        'shop_name'=>'required',
        'shop_address'=>'required',
        'shop_phone'=>'required',
        'shop_start'=>'required',
        'shop_end'=>'required',
        'shop_email'=>'required',
        'business_hours' => 'required',
    ];//新增验证
    protected $BaseCreate =[
        'shop_name'=>'',
        'shop_address'=>'',
        'shop_phone'=>'',
        'shop_start'=>'',
        'shop_end'=>'',
        'shop_email'=>'',
        'status'=>'',
        'business_hours'=>'',
        'come_status'=>'',
        'mail_status'=>'',
        'create_time' => ['type','date'],
    ];//新增数据

    protected $BaseUpdateVat = [
        'id' =>        'required',
    ];//新增验证
    protected $BaseUpdate =[
        'id'=>'', 'shop_name'=>'',
        'shop_address'=>'',
        'shop_phone'=>'',
        'shop_start'=>'',
        'shop_end'=>'',
        'shop_email'=>'',
        'status'=>'',
        'business_hours'=>'',
        'come_status'=>'',
        'mail_status'=>'',
        'update_time' => ['type','date'],
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据
}
