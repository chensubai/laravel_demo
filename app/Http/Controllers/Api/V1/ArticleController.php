<?php
    
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\BaseController;
use Illuminate\Http\Request;
use App\Transformers\AuthorizationTransformer;
use Illuminate\Support\Facades\Hash;
use Dingo\Api\Exception\StoreResourceFailedException;

class ArticleController extends BaseController
{
    protected $BaseModels = 'App\Models\Article';
    protected $BaseAllVat = [];//获取全部验证
    protected $BAWhere  = ['id'=>['=',''],'catid'=>['=',''],'ascription'=>['=','']];//获取全部Where条件
    protected $BA  = ['*'];//获取全部选取字段 *是全部
    protected $BAOrder  = [['sort','desc']];//获取全部字段排序

    protected $BaseLVat = [];//获取分页全部验证
    protected $BLWhere  = ['id'=>['=',''],'catid'=>['=',''],'ascription'=>['=','']];//获取全部分页Where条件
    protected $BL  = ['*'];//获取全部分页选取字段 *是全部
    protected $BLOrder  = [['sort','asc']];//获取全部分页字段排序

    protected $BaseOneVat = ['id' => 'required',];//单个处理验证
    protected $BOWhere = ['id'=>['=','']];//单个查询验证
    protected $BO = ['*'];//单个选取字段；*是全部
    protected $BOOrder = [['id','desc']];//单个选取字段排序

    protected $BaseCreateVat = [
        'title' => 'required',//标题
        'simple_intro' => 'required',//简介
        'content' => 'required',//内容
        'img_url' => 'required',//链接地址
        // 'sort' => 'required',//排序
        'status' => 'required',//是否禁用（0不展示 1展示）',
    ];//新增验证
    protected $BaseCreate =[
        'title'=>'','simple_intro'=>'','content'=>'',
        'img_url'=>'','sort'=>'','status'=>'',
        'createtime' => ['type','time'],
    ];//新增数据

    protected $BaseUpdateVat = [
        'id' =>        'required',
    ];//新增验证
    protected $BaseUpdate =[
        'id'=>'','title'=>'','simple_intro'=>'',
        'content'=>'','img_url'=>'','sort'=>'','status'=>'',
        'updatetime' => ['type','time'],
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据
}
