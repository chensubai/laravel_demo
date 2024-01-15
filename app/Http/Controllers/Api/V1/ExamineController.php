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
use App\Models\Member;

class ExamineController extends BaseController
{
    protected $BaseModels = 'App\Models\Examine';
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
        // 'title' => 'required',//标题
        // 'content' => 'required',//描述
        // 'imagepath' => 'required',//图片地址
        // 'line_url' => 'required',//链接地址
        // // 'listorder' => 'required',//排序
        // 'status' => 'required',//是否禁用（1：正常； 2：禁用）',
        // 'type' => 'required',//图片类型:1=首页轮播,2=会员轮播',
        // `createtime` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
        // `updatetime` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
    ];//新增验证
    protected $BaseCreate =[
        'username'=>'','uid'=>'','name'=>'',
        'sex'=>'','birth'=>'','age'=>'',
        'addr'=>'','phone_number'=>'','signature'=>'',
        'occupation'=>'','bank_name'=>'','branch_name'=>'',
        'port_category'=>'','slogans'=>'','name_mouth'=>'',
        'positive'=>'','other_side'=>'',
        'createtime' => ['type','time'],
    ];//新增数据

    protected $BaseUpdateVat = [
        'id' =>        'required',
    ];//新增验证
    protected $BaseUpdate =[
        'id'=>'', 'username'=>'','uid'=>'','name'=>'',
        'sex'=>'','birth'=>'','age'=>'',
        'addr'=>'','phone_number'=>'','signature'=>'',
        'occupation'=>'','bank_name'=>'','branch_name'=>'',
        'port_category'=>'','slogans'=>'','name_mouth'=>'',
        'positive'=>'','other_side'=>'',
        // 'updatetime' => ['type','time'],
    ];//新增数据
    protected $BUWhere= ['id'=>['=','']];//新增数据
      /**
     *基础取单个值方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseOne(Request $request)
    {
        $id = Auth::id();
        $where_data[]= ['uid','=',$id];
        $RData = (new $this->BaseModels)->BaseOne($where_data,$this->BO,$this->BOOrder);
        return  $this->success($RData,__('base.success'));
    }
     /**
     *基础新增方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseCreate(Request $request)
    {
        $create_data = [];
        $validator = Validator::make($request->all(), $this->BaseCreateVat);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $uid = Auth::id();
        $RData = (new $this->BaseModels)->BaseOne([['uid','=',$uid]],$this->BO,$this->BOOrder);
        if(empty($RData) || $RData ==NULL)  return  $this->error();
    
        $data = $request->all();
        //根据配置传入参数
        foreach($this->BaseCreate as $k => $v){
            if(!empty($data[$k])){
                $create_data[$k] = $data[$k];
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'time'){
                $create_data[$k] = time();
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'date'){
                $create_data[$k] = date('Y-m-d H:i:s');
            }
        }
        $create_data['uid'] = $uid;
        //修改参数  request要存在或者BUWhere存在
        $id = (new $this->BaseModels)->BaseCreate($create_data);
        if($id){
            $data['id'] = $id;
            DB::table('member')->where('id','=',$uid)->update(['status'=>2]);
            return  $this->success($data,__('base.success'));
        }else{
            return  $this->error();
        }
    }

    /**
     *基础修改方法
     *
     * @param Request $request
     * @return void
     */
    public function BaseUpdate(Request $request)
    {
        $where_data= [];
        $update_data = [];
        $validator = Validator::make($request->all(), $this->BaseUpdateVat);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        //根据配置传入参数
        foreach($this->BaseUpdate as $k => $v){
            if(!empty($data[$k])){
                $update_data[$k] = $data[$k];
            }
            if(!empty($v) && empty($v[1])){
                $update_data[$k] = $v;
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'time'){
                $update_data[$k] = time();
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'date'){
                $update_data[$k] = date('Y-m-d H:i:s');
            }
        }
        //修改参数  request要存在或者BUWhere存在
        foreach($this->BUWhere as $B_k => $B_v){
            if(!empty($data[$B_k]) && empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && !empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $RData = (new $this->BaseModels)->BaseUpdate($where_data,$update_data);
        if($RData){
            $uid = Auth::id();
            DB::table('member')->where('id','=',$uid)->update(['status'=>2]);
            return  $this->success($data,__('base.success'));
        }else{
            return  $this->error();
        }
    }

    public function memberUpdate(Request $request){
        $where_data= [];
        $update_data = [];
        $BaseUpdateVat = [
            'id' =>        'required',
        ];
        $BaseUpdate =[
            'id'=>'',
            'nickname'=>'',
            'password'=>['type','password'],
            'mobile'=>'',
            'avatar'=>'',
            'email'=>'',
        ];//新增数据
        $BUWhere= ['id'=>['=','']];
        $validator = Validator::make($request->all(), $BaseUpdateVat);
        if ($validator->fails()) return $this->errorBadRequest($validator);
        $data = $request->all();
        if(!empty($data['username']) ){
            $username  = DB::table('member')->where('id','!=',$data['id'])->where('username','=',$data['username'])->count();
            if( $username > 0){
                return  $this->error(__('base.userCreate'));
            }
        }
        //根据配置传入参数
        foreach($BaseUpdate as $k => $v){
            if(!empty($data[$k])){
                $update_data[$k] = $data[$k];
            }
            if(!empty($v) && empty($v[1])){
                $update_data[$k] = $v;
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'time'){
                $update_data[$k] = time();
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'date'){
                $update_data[$k] = date('Y-m-d H:i:s');
            }
            if(!empty($v[0]) && !empty($v[1]) && $v[0]== 'type' && $v[1]== 'password'){
                //验证旧密码是否正确
                if(!empty($data[$k])){
                    $password = encrypt_password($data[$k]);
                    $update_data['password'] = $password['password'];
                    $update_data['encrypt'] =  $password['encrypt'];
                }
            }
        }
        //修改参数  request要存在或者BUWhere存在
        foreach($BUWhere as $B_k => $B_v){
            if(!empty($data[$B_k]) && empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$data[$B_k]];
            }
            if(!empty($B_v) && !empty($B_v[1])){
                $where_data[] = [$B_k,$B_v[0],$B_v[1]];
            }
        }
        $RData = (new Member)->BaseUpdate($where_data,$update_data);
        if($RData)
            return  $this->success($data,__('base.success'));
        else
            return  $this->error();
    }

}
