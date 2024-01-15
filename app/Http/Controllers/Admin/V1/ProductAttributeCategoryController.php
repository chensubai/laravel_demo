<?php
    
namespace App\Http\Controllers\Admin\V1;

use App\Http\Controllers\Admin\V1\BaseController;
use Illuminate\Http\Request;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;

class ProductAttributeCategoryController extends BaseController
{
    protected $BaseModels = 'App\Models\ProductAttributeCategory';
    protected $BaseAllVat = [];//获取全部验证
    protected $BAWhere  = [];//获取全部Where条件
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
        'name' => 'required',//
    ];//新增验证
    protected $BaseCreate =[
        'name'=>''
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
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据
}
