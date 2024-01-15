<?php
    
namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Admin\V1\BaseController;
use Illuminate\Http\Request;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;
use App\Models\ProductAttributeCategory;
use Illuminate\Support\Facades\DB;

class ProductAttributeController extends BaseController
{
    protected $BaseModels = 'App\Models\ProductAttribute';
    protected $BaseAllVat = [];//获取全部验证
    protected $BAWhere  = [];//获取全部Where条件
    protected $BA  = ['*'];//获取全部选取字段 *是全部
    protected $BAOrder  = [['id','desc']];//获取全部字段排序

    protected $BaseLVat = [
        'product_attribute_category_id' => 'required',
        'hand_add_status' => 'required',
    ];//获取分页全部验证
    protected $BLWhere  = [
        'product_attribute_category_id'=>['=',''],
        'hand_add_status'=>['=','']
    ];//获取全部分页Where条件
    protected $BL  = ['*'];//获取全部分页选取字段 *是全部
    protected $BLOrder  = [['id','desc']];//获取全部分页字段排序

    protected $BaseOneVat = ['id' => 'required',];//单个处理验证
    protected $BOWhere = ['id'=>['=','']];//单个查询验证
    protected $BO = ['*'];//单个选取字段；*是全部
    protected $BOOrder = [['id','desc']];//单个选取字段排序

    protected $BaseCreateVat = [
        'name' => 'required',//属性名称
        'product_attribute_category_id' => 'required',//分类id
        'select_type' => 'required',//属性选择类型：0->唯一；1->单选；2->多选
        'input_list' => 'required',//属性录入方式：0->手工录入；1->从列表中选取
        'input_list' => 'required',//可选值列表，以逗号隔开
        'sort' => 'required',//排序
        'name' => 'required',//属性名称
        'hand_add_status' => 'required',//是否支持手动新增；0->不支持；1->支持
        'type' => 'required',//属性的类型；0->规格；1->参数
        'm_pic' => 'required',//男尺码图片（日文）
        'm_pic_cn' => 'required',//男尺码图片（中文）
        'f_pic' => 'required',//女尺码图片（日文）
        'f_pic_cn' => 'required',//女尺码图片（中文）
    ];//新增验证
    protected $BaseCreate =[
        'name_upd'=>'',
        'name' => '',//属性名称
        'product_attribute_category_id' => '',//分类id
        'select_type' => '',//属性选择类型：0->唯一；1->单选；2->多选
        'input_list' => '',//属性录入方式：0->手工录入；1->从列表中选取
        'input_list' => '',//可选值列表，以逗号隔开
        'sort' => '',//排序
        'name' => '',//属性名称
        'hand_add_status' => '',//是否支持手动新增；0->不支持；1->支持
        'type' => '',//属性的类型；0->规格；1->参数
        'm_pic' => '',//男尺码图片（日文）
        'm_pic_cn' => '',//男尺码图片（中文）
        'f_pic' => '',//女尺码图片（日文）
        'f_pic_cn' => '',//女尺码图片（中文）
        // 'createtime' => ['type','time'],
    ];//新增数据
    protected $BaseUpdateVat = [
        'id' =>        'required',
        'name' => 'required',//上级ID
        // 'order' => 'required',//菜单排序
        // 'title' => 'required',//标题
        // 'icon' => 'required',//图标
        // 'uri' => 'required',//URI
        // 'routes' => 'required',//路由
    ];//新增验证
    protected $BaseUpdate =[
        'id'=>'','name'=>'',
        'name_upd'=>'',
        'name' => '',//属性名称
        'product_attribute_category_id' => '',//分类id
        'select_type' => '',//属性选择类型：0->唯一；1->单选；2->多选
        'input_list' => '',//属性录入方式：0->手工录入；1->从列表中选取
        'input_list' => '',//可选值列表，以逗号隔开
        'sort' => '',//排序
        'name' => '',//属性名称
        'hand_add_status' => '',//是否支持手动新增；0->不支持；1->支持
        'type' => '',//属性的类型；0->规格；1->参数
        'm_pic' => '',//男尺码图片（日文）
        'm_pic_cn' => '',//男尺码图片（中文）
        'f_pic' => '',//女尺码图片（日文）
        'f_pic_cn' => '',//女尺码图片（中文）
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据

    public function _limitFrom($RData)
    {
        if(!empty($RData['data'])){
            foreach($RData['data'] as &$v){
                if(!empty($RData))
                {
                    $name  = DB::table('pms_product_attribute_category')->where('id','=',$v['product_attribute_category_id'])->pluck('name');
                    $v['product_attribute_category_name'] =  !empty($name[0]) ? $name[0]: '';
                }
            }
        }
        return $RData;
    }
}
